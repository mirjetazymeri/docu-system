# Tax architecture

Tax is a small catalog (classes, rates, rules) plus a calculation adapter (`TaxService`) that feeds the shared pricing domain and checkout snapshots.

## Components

```
Admin CRUD                    Runtime
───────────                   ───────
TaxClassController            TaxSettings  ←  setting('tax.*'), store.country
TaxRateController             TaxService
  FormRequests                    │
  tax_classes / tax_rates         ├─ applicableRates (SQL + zone filter)
  tax_rules pivot                 ├─ CouponService (discount-eligible line ids)
                                  └─ OrderTotalsCalculator
                                       └─ TaxInclusivePriceCalculator
                                            TaxRateInput / TaxBreakdown / Money
```

Admin and checkout share the same tables. Checkout never writes `tax_rates`; it only reads active rates attached to a class.

## Catalog model

- A **tax class** is a named code (`standard`, etc.) assigned to products (`products.tax_class_id`) and optionally to shipping (`tax.shipping.tax_class_code`).
- A **tax rate** is a percent plus a geographic predicate (country / state / postcode or ZIP range), priority, compound flag, and active flag.
- **tax_rules** is many-to-many. Admin `sort_order` is stored on the pivot; **matching order is `tax_rates.priority` then `id`**, not pivot sort.

A product without a class, or a class with no matching active rates for the destination, contributes **zero** line tax.

## Destination

Used only at quote time (not stored as a tax jurisdiction id):

1. Cart `shipping` JSON if present, else `billing`
2. Else `TaxSettings` origin (`tax.origin.*`, country falling back to `store.country`, then `AL`)

Address keys: `country_code` or `country`; `state_code` or `state`; `postcode`.

Empty ZIP skips postcode and ZIP-range predicates (those rates still match on country/state).

ZIP ranges use **string** `strcmp`, not numeric ZIP math.

## Inclusive vs exclusive

Setting `tax.pricing.price_includes_tax` (default **true**):

| Mode | Line tax | Grand total |
|------|----------|-------------|
| Inclusive | Extract tax from (possibly discounted) gross | `subtotal - discount + shipping + deposit` (tax already inside subtotal/shipping if those amounts include tax) |
| Exclusive | Add tax on (possibly discounted) net-ish gross | Same formula **plus** `total_tax` |

`CartService` documents that inclusive `tax_total` is for display and must not be added again; `OrderTotalsCalculator` implements that.

## Discounts and tax

If `tax.calculation.discounts_reduce_taxable_amount` (default true), each line’s taxable amount is `row_total − allocated_discount` (not below zero). Coupon product/category/attribute scope is applied via `CouponService::eligibleCartItemIds`.

`TaxService` also estimates `discount_tax` / `discount_net` for API envelopes (`FinancialTotalsResource::fromTaxQuote`). Order items store allocated `discount` and `tax` from the calculator’s `line_*` maps.

## Shipping tax

Off by default (`tax.shipping.taxable`). When on, rates come from the tax class whose `code` equals `tax.shipping.tax_class_code` (default `standard`), same destination matching as lines. There is no seeder guaranteeing that class exists.

Deposits (`cart` item `deposit_total`) are summed into the quote and added to grand total; they are not run through `applicableRates`.

## Compound vs combined simple rates

`TaxInclusivePriceCalculator`:

- Multiple **non-compound** inclusive rates: **one combined extraction**, then split the tax pool by rate share (tested: two 10% rates on 120 → 20.00 tax, not stacked 10%+10% on a shrinking base).
- **Compound** inclusive rates: sequential extraction on the remaining inclusive base after simple rates.
- Exclusive: each rate in list order; compound uses `amount + tax so far` as base.

## Snapshots (this module’s contribution)

### Cart (volatile)

Every `CartService::recalcTotals`:

- Denormalized totals columns including `tax_total` / `grand_total`
- `meta.tax_quote` = full `TaxService::calculate` payload (line maps + breakdown + nets)

Changing rates, classes, product class, address, or settings changes the next recalc. The cart is not a legal snapshot.

### Order (frozen)

`OrderService::place` recalculates immediately before insert and copies:

- Header: `tax_total`, `prices_include_tax`, `tax_breakdown` (aggregated by rate code)
- Lines: `tax`, `meta.net_total`, `meta.tax_total`

Later edits to `tax_rates` / `tax_classes` / product `tax_class_id` do not rewrite orders. Breakdown stores `code`, `label`, `rate`, `amount` — not rate row ids.

Credit notes and invoices read these columns; they do not re-run `TaxService`.

## Admin vs runtime permissions

Admin CRUD is `role:Admin` plus `tax_class_*` / `tax_rate_*`. Checkout calculation has no tax permission; any cart recalc/place-order path that already can mutate the cart will tax it.

## Rounding

Domain money uses BCMath strings. Display/persistence scale for totals is **2 decimal places**, half-up (`Money::round`). Intermediate tax pools use a higher scale (4) inside `OrderTotalsCalculator` before rounding lines.

## Extension points (as the code exists)

- New jurisdictions: add `TaxRate` rows (and attach via `tax_rules`); matching fields are the columns above — there is no plugin/strategy interface.
- New calculation flags: `config/settings.php` `tax` schema + `TaxSettings::config()`.
- Alternate math: would require changing `TaxInclusivePriceCalculator` / `OrderTotalsCalculator` (shared with all callers).
