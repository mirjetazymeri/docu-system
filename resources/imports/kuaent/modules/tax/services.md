# Tax services and domain tax types

## TaxSettings

**File:** `app/Services/Tax/TaxSettings.php`

Resolves admin settings (`setting()`) against schema defaults. No cache of its own.

### `config(): array`

| Key | Setting key | Default if unset |
|-----|-------------|------------------|
| `price_includes_tax` | `tax.pricing.price_includes_tax` | `true` |
| `discounts_reduce_taxable_amount` | `tax.calculation.discounts_reduce_taxable_amount` | `true` |
| `shipping_taxable` | `tax.shipping.taxable` | `false` |
| `shipping_tax_class_code` | `tax.shipping.tax_class_code` | `'standard'` |
| `origin.country` | `tax.origin.country`, else `store.country` | `'AL'` (then `strtoupper` / trim) |
| `origin.state` | `tax.origin.state` | `''` |
| `origin.postcode` | `tax.origin.postcode` | `''` |

Booleans go through `bool()`: null → default; bool as-is; int/float `=== 1`; strings `1/true/on/yes` vs `0/false/off/no/''`; otherwise `filter_var(..., FILTER_VALIDATE_BOOLEAN)`.

### `get(string $key, mixed $default = null)`

`data_get` on `config()`.

Schema definitions: `config/settings.php` group `tax` (labels, types, `public` flags). `tax.pricing.price_includes_tax` is `public => true`; other tax settings in that group are not.

## Helper `tax_config()`

**File:** `app/helpers.php`

```php
tax_config(?string $key = null, mixed $default = null): mixed
```

Resolves `TaxSettings::config()`. `null` key returns the full array; otherwise `data_get($config, $key, $default)`.

Callers outside this module (read-only): `CartResource`, `CartController::totals` fallback, `SalesSpecialistOrderService::buildPreviewResponse` (`price_includes_tax`).

## TaxService

**File:** `app/Services/Tax/TaxService.php`

Constructor-injected: `TaxSettings`, `CouponService`. Instantiates `OrderTotalsCalculator` and `TaxInclusivePriceCalculator` internally (not the container).

Not bound in a service provider; Laravel auto-wires it.

### `calculate(Cart $cart): array`

Authoritative cart/order tax quote.

**Inputs from cart**

| Source | Use |
|--------|-----|
| `$cart->items` | Each line: `id`, `row_total` as gross, `product->tax_class_id`, `deposit_total` sum |
| `$cart->coupon_discount`, `$cart->coupon_id` | Discount total and eligible line IDs |
| `$cart->shipping` / `$cart->billing` | Destination and shipping gross (`shipping.amount`) |

**Destination** (`destination()`): prefer `$cart->shipping`, else `$cart->billing`, else origin from settings.

| Field | Keys tried | Fallback |
|-------|------------|----------|
| Country | `country_code`, then `country` | `origin.country` or `'AL'` |
| State | `state_code`, then `state` | `origin.state` or `''` |
| ZIP | `postcode` | `origin.postcode` or `''` |

Country is `strtoupper(trim(...))`.

**Per merchandise line**

- Rates: `applicableRates($taxClassId, $country, $state, $zip)` → `TaxRateInput[]`.
- `discount_eligible`: `true` unless a coupon is applied with a positive `coupon_discount` and `CouponService::eligibleCartItemIds` returns a list that excludes the cart item id. Missing coupon row → all lines eligible (`null`).

**Shipping**

If `shipping_taxable`: load `TaxClass` where `code` = `shipping_tax_class_code`, then `applicableRates` for that class. Missing class or empty rates → no shipping tax.

Shipping gross: `(string) data_get($cart->shipping, 'amount', 0)`.

**Delegation**

`OrderTotalsCalculator::calculate(...)` with settings `price_includes_tax`, `discounts_reduce_taxable_amount`, `shipping_taxable`.

**Return keys** (PHPDoc on the method)

| Key | Meaning |
|-----|---------|
| `items_tax` | Tax on merchandise lines |
| `shipping_tax` | Tax on shipping (0 if not taxable) |
| `total_tax` | Items + shipping tax |
| `breakdown` | List of `{code, label, rate, amount}` aggregated by rate code |
| `line_taxes` / `line_discounts` / `line_nets` | Maps keyed by **cart item id** |
| `subtotal` | Sum of line grosses |
| `discount_total` | Coupon/discount gross used in totals |
| `shipping_total` | Shipping gross |
| `deposit_total` | Sum of item `deposit_total` (added to grand; not taxed as a line) |
| `grand_total` | Inclusive: `subtotal - discount + shipping + deposit`. Exclusive: that plus `total_tax` |
| `price_includes_tax` | From settings |
| `items_net` | Sum of `line_nets`, rounded to 2 |
| `discount_tax` | Estimated tax portion of allocated line discounts (`estimateDiscountTax`) |
| `discount_net` | `discount_total - discount_tax` |
| `shipping_net` | `shipping_total - shipping_tax` |

`estimateDiscountTax` re-runs `taxOnAmount` on each line’s allocated discount with that line’s rates (display/API net-tax split; order place uses `line_*` maps, not this estimate, for item columns).

### `applicableRates(?int $taxClassId, ?string $country, ?string $state, ?string $zip): Collection`

- `null` / missing class id → empty collection.
- SQL: `tax_rates` inner join `tax_rules` on `tax_rate_id`, `tax_rules.tax_class_id` = class, `tax_rates.is_active` = true, order `priority`, `id`.
- Then PHP filter (country / state / ZIP) described in [models.md](models.md).

Returns a `Collection` of `TaxRate` models (values reset).

### `taxOnAmount(float $amount, Collection $rates, bool $priceIncludesTax): array`

Legacy wrapper around `TaxInclusivePriceCalculator::taxOnAmount`. Returns `[float tax rounded to 2, list of {code, label, rate, amount}]`. No in-repo callers besides `TaxService` itself.

### `toRateInputs(Collection $rates): TaxRateInput[]`

Maps `code`, `name` → `label`, `rate_percent`, `compound`, `priority`.

## Domain: TaxRateInput

**File:** `app/Domain/Pricing/TaxRateInput.php`

Immutable descriptor (no Eloquent): `code`, `label`, `ratePercent` (string), `compound` (default false), `priority` (default 0).

`priority` is not used inside `TaxInclusivePriceCalculator`; order of the array is what matters. `TaxService` already sorted rates in SQL.

## Domain: TaxBreakdown

**File:** `app/Domain/Pricing/TaxBreakdown.php`

Immutable `net`, `tax`, `gross` (`Money`) plus `ratePercent` string. `rounded(int $scale = 2)` returns a new instance with each money rounded.

Used by `TaxInclusivePriceCalculator::extract()` (single rate). Multi-rate path returns `[Money, components[]]` instead.

## Domain: TaxInclusivePriceCalculator

**File:** `app/Domain/Pricing/TaxInclusivePriceCalculator.php`

Shared pricing math. Tax usage:

### `taxOnAmount(Money $amount, list<TaxRateInput> $rates, bool $priceIncludesTax)`

Zero amount or empty rates → `[Money::zero, []]`.

- Inclusive (`true`): `extractInclusive`
- Exclusive (`false`): `addExclusive`

**Inclusive, non-compound (simple) rates:** combined rate `sum(rate/100)`, then `net = amount / (1 + combined)`, `taxPool = amount - net`. Tax is split across simple rates by each rate’s share of the combined rate; the last simple rate gets the remainder (avoids double extraction).

**Inclusive, compound rates:** after simple extraction, each compound rate is extracted sequentially from the remaining inclusive base: `portion = base - base / (1 + rate)`.

**Exclusive:** for each rate in array order, `base = compound ? amount + taxSoFar : amount`, `portion = base * (rate/100)`.

### `extract(Money $grossAmount, string $ratePercent): TaxBreakdown`

Single-rate inclusive split: `net = gross / (1 + rate/100)`, `tax = gross - net`.

BCMath via `Money`. `Money::round()` is half-up.

## Domain: OrderTotalsCalculator (tax usage only)

**File:** `app/Domain/Pricing/OrderTotalsCalculator.php`

Pure function (no Eloquent / settings). Tax-related behavior:

1. Optionally allocate `discountTotal` across `discount_eligible` lines (`DiscountAllocator`).
2. Per line, taxable gross = `gross - lineDiscount` if `discountsReduceTaxable`, else full gross (floored at zero).
3. `taxOnAmount` on that taxable gross with the line’s `TaxRateInput[]`.
4. Line net = taxable gross − tax, rounded to 2. Line tax rounded to 2.
5. If `shippingTaxable` and shipping is positive and `shippingRates` is non-empty: tax shipping the same way (inclusive or exclusive flag).
6. Breakdown map keyed by rate **code**, amounts summed then rounded to 2.
7. Grand total: if `priceIncludesTax`, do **not** add `total_tax` again (`subtotal - discount + shipping + deposit`). If exclusive, add `total_tax`.

This class is shared with checkout totals; this module is the production caller that supplies rates and settings flags (`TaxService`).
