# Tax FormRequests

Namespace: `App\Http\Requests\Admin\Tax`.

All four: `authorize()` returns `true`. Permissions are controller middleware.

## StoreTaxClassRequest

**File:** `app/Http/Requests/Admin/Tax/StoreTaxClassRequest.php`

| Field | Rules |
|-------|--------|
| `code` | required, string, max 64, unique `tax_classes,code` |
| `name` | required, string, max 191 |
| `rate_ids` | sometimes, array |
| `rate_ids.*` | integer, exists `tax_rates,id` |
| `rate_sort_orders` | sometimes, array |

`rate_sort_orders` values are **not** typed in validation; `syncRates` casts them with `(int)`.

`TaxClass::create($req->validated())` only persists fillable `code` / `name`. Pivot sync uses request `rate_ids` / `rate_sort_orders`.

## UpdateTaxClassRequest

**File:** `app/Http/Requests/Admin/Tax/UpdateTaxClassRequest.php`

Extends `StoreTaxClassRequest`. Replaces `code` uniqueness with `Rule::unique('tax_classes','code')->ignore($id)` where `$id = (int) $this->route('id')` (route parameter `{id}`).

## StoreTaxRateRequest

**File:** `app/Http/Requests/Admin/Tax/StoreTaxRateRequest.php`

### `prepareForValidation()`

Merges:

| Field | Transform |
|-------|-----------|
| `is_active` | `(bool) $this->boolean('is_active')` |
| `compound` | `(bool) $this->boolean('compound')` |
| `is_zip_range` | `(bool) $this->boolean('is_zip_range')` |
| `country_iso2` | `strtoupper` if present, else `null` |
| `state_iso2` | `strtoupper` if present, else `null` |

Unchecked checkboxes become `false` via `boolean()`.

### `rules()`

| Field | Rules |
|-------|--------|
| `code` | required, string, max 64, unique `tax_rates,code` |
| `name` | required, string, max 191 |
| `country_iso2` | nullable, string, size 2 |
| `state_iso2` | nullable, string, max 8 |
| `postcode` | nullable, string, max 32, **prohibited_if:is_zip_range,1** |
| `is_zip_range` | sometimes, boolean |
| `zip_from` | required_if `is_zip_range,1`, nullable, string, max 32 |
| `zip_to` | required_if `is_zip_range,1`, nullable, string, max 32 |
| `rate_percent` | required, numeric, min 0, max 1000 |
| `priority` | nullable, integer, min 0 |
| `compound` | sometimes, boolean |
| `is_active` | sometimes, boolean |

### `withValidator` after hook

If `is_zip_range` is true and either `zip_from` or `zip_to` is empty: error on `zip_from` — `ZIP range requires both zip_from and zip_to.`

(Overlaps `required_if` on those fields.)

`country_iso2` is **not** `exists:countries,code`. Invalid ISO2 still stores.

## UpdateTaxRateRequest

**File:** `app/Http/Requests/Admin/Tax/UpdateTaxRateRequest.php`

Extends `StoreTaxRateRequest`. Unique `code` ignores route `{id}`. Inherits `prepareForValidation` and the after hook.

## Settings (not FormRequests)

Tax calculation flags are validated through `SettingController` using `config/settings.php` field types (`boolean` / `text` / select of class codes). There is no `App\Http\Requests\Admin\Tax\*` class for settings.

## Product assignment (not this module)

`StoreProductRequest` (and product update via the same tax rule): `tax_class_id` nullable integer `exists:tax_classes,id`.
