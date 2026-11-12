# Tax

Admin-managed **tax classes** and **tax rates**, plus checkout **tax calculation** and **order snapshots**.

Catalog prices are quoted through this module’s settings (`tax.pricing.price_includes_tax`). At cart recalc and order place, `TaxService` selects active rates for each line’s tax class and destination, then the shared pricing domain (`OrderTotalsCalculator` / `TaxInclusivePriceCalculator`) computes tax, discounts, and grand total. Place-order copies that quote onto the order and each order item so later catalog or rate edits do not change historical invoices.

## What this module owns

- Admin CRUD for tax classes (`TaxClass`) and tax rates (`TaxRate`)
- Class–rate attachments (`tax_rules`)
- Runtime rate matching by class + destination (`TaxService::applicableRates`)
- Cart/order tax quotes (`TaxService::calculate`)
- Resolved tax settings (`TaxSettings`, `tax_config()` helper, `config/settings.php` `tax` schema)
- Tax usage of shared domain types: `TaxRateInput`, `TaxBreakdown`, `TaxInclusivePriceCalculator`

## What this module does not own

- Product CRUD (products only store `tax_class_id`; assignment UI lives in the product admin)
- Cart line pricing, coupons, shipping quotes, or payment (checkout uses this module’s quote)
- Order lifecycle beyond persisting tax fields at place-time
- Credit-note math (reuses the order’s stored tax snapshot; does not call `TaxService`)
- Shared money/discount allocation internals (`Money`, `DiscountAllocator`) except where tax calculation uses them

## Admin entry

- Sidebar **Tax** (visible with `tax_class_view` or `tax_rate_view`)
  - Tax classes: `/admin/tax-classes`
  - Tax rates: `/admin/tax-rates`
- Calculation flags: Admin **Settings** → schema group `tax` (`config/settings.php`)

## Runtime flow (short)

1. Product has optional `tax_class_id`.
2. Cart recalc / place-order calls `TaxService::calculate($cart)`.
3. Destination = cart shipping address, else billing, else tax origin settings.
4. Applicable rates = active rates attached to the class via `tax_rules`, filtered by country / state / postcode or ZIP range.
5. `OrderTotalsCalculator` extracts or adds tax per line (and optionally shipping).
6. Cart stores totals + `meta.tax_quote`. Order stores `tax_total`, `prices_include_tax`, `tax_breakdown`; items store `tax` and `meta.net_total` / `meta.tax_total`.

## Documentation index

| File | Contents |
|------|----------|
| [architecture.md](architecture.md) | Classes, rates, matching, calculation, snapshots |
| [models.md](models.md) | `TaxClass`, `TaxRate` |
| [database.md](database.md) | Tables, FKs, order/cart/product tax columns |
| [controllers.md](controllers.md) | Admin tax class/rate controllers |
| [services.md](services.md) | `TaxService`, `TaxSettings`, domain tax types |
| [requests-validation.md](requests-validation.md) | Admin FormRequests |
| [routes.md](routes.md) | Admin routes, permissions, settings |
| [dependencies.md](dependencies.md) | Callers and shared pricing |
| [tests.md](tests.md) | Unit coverage of tax extraction / totals |

There is no tax-class seeder. Shipping tax looks up class code `tax.shipping.tax_class_code` (schema default `standard`); if that class is missing, shipping tax is zero.
