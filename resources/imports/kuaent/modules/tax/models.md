# Tax models

Eloquent models owned by this module: `App\Models\TaxClass` and `App\Models\TaxRate`.

There is no Eloquent model for `tax_rules`; it is the pivot table on the class–rate `belongsToMany`.

`Product` has a `tax_class_id` column and no `taxClass()` relation. `TaxService` reads `optional($item->product)->tax_class_id`.

## TaxClass

**File:** `app/Models/TaxClass.php`

| | |
|--|--|
| Table | `tax_classes` |
| Fillable | `code`, `name` |
| Casts | none |

### Relations

| Method | Type | Pivot / notes |
|--------|------|----------------|
| `rates()` | `belongsToMany(TaxRate::class, 'tax_rules')` | Pivot `sort_order`; timestamps on pivot |

`code` is unique (migration + admin validation). Schema comments suggest values such as `standard`, `reduced`, `zero`; the application does not seed them.

Admin create/update may send `rate_ids` / `rate_sort_orders`. Those are **not** fillable; they are applied only via `TaxClassController::syncRates()`.

Deleting a class:

- `tax_rules` rows cascade-delete (`cascadeOnDelete` on `tax_class_id`)
- `products.tax_class_id` is set to null (`nullOnDelete`)

## TaxRate

**File:** `app/Models/TaxRate.php`

| | |
|--|--|
| Table | `tax_rates` |
| Fillable | `code`, `name`, `country_iso2`, `state_iso2`, `postcode`, `is_zip_range`, `zip_from`, `zip_to`, `rate_percent`, `priority`, `compound`, `is_active` |
| Casts | `is_zip_range` boolean, `compound` boolean, `is_active` boolean, `rate_percent` `decimal:4` |

### Relations

| Method | Type | Notes |
|--------|------|--------|
| `classes()` | `belongsToMany(TaxClass::class, 'tax_rules')` | Same pivot as `TaxClass::rates()` (`sort_order`, timestamps) |
| `country()` | `belongsTo(Country::class, 'country_iso2', 'code')` | Display join for admin DataTables. **No FK** from `tax_rates.country_iso2` to `countries` |

### Zone fields (matching)

Used by `TaxService::applicableRates()` (PHP filter after SQL join):

| Column | Match behavior |
|--------|----------------|
| `country_iso2` | If set, must equal destination country (case-insensitive). If null, any country. |
| `state_iso2` | If set, must equal destination state (case-insensitive). If null, any state. |
| `is_zip_range` + `zip_from` / `zip_to` | If range flag is true **and** destination ZIP is non-empty: `strcmp(zip, zip_from) >= 0` and `strcmp(zip, zip_to) <= 0` (string compare). |
| `postcode` | If set **and** destination ZIP is non-empty **and** the ZIP-range branch did not already return: exact match, case-insensitive. |

If destination ZIP is empty, postcode and ZIP-range filters are skipped; the rate still matches on country/state (and class + active).

### Calculation fields

| Column | Role |
|--------|------|
| `rate_percent` | Percent (e.g. `20.0000`), passed to `TaxRateInput::$ratePercent` as a string |
| `priority` | SQL order: `priority` ASC, then `id` ASC. Lower first. |
| `compound` | Compound vs simple in `TaxInclusivePriceCalculator` |
| `is_active` | Inactive rates are excluded from `applicableRates` |
| `code` / `name` | Snapshot labels in tax breakdown (`code`, `label`) |

Pivot `tax_rules.sort_order` is stored by admin attach UI. **Rate selection for checkout does not order by `sort_order`**; it uses `tax_rates.priority` then `tax_rates.id`.

Deleting a rate cascade-deletes its `tax_rules` rows.

## Related models (not owned)

These are documented only where tax reads or writes them.

| Model | Tax-related fields |
|-------|--------------------|
| `Product` | `tax_class_id` (integer, fillable, nullable FK) |
| `Cart` | `tax_total`; `meta.tax_quote` (full quote); `shipping` / `billing` JSON for destination and shipping amount |
| `CartItem` | `row_total` (line gross); `deposit_total` (summed into quote, not taxed as a merchandise line); `product()` for `tax_class_id` |
| `Order` | `tax_total`, `prices_include_tax`, `tax_breakdown` (JSON array) |
| `OrderItem` | `tax`; `meta.net_total`, `meta.tax_total` |
| `Country` | Optional name lookup for admin rate list via `code` |
