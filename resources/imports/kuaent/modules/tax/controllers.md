# Tax admin controllers

Both live under `App\Http\Controllers\Admin`. They are HTML + Yajra DataTables JSON on the same `index` URL (`$request->ajax()`).

FormRequests: [requests-validation.md](requests-validation.md). Routes: [routes.md](routes.md).

## Shared behavior

- Constructor `check.permission:*` middleware (see [routes.md](routes.md)).
- `authorize()` on FormRequests is `true`; access control is middleware + Blade `@can`.
- Success flash: `session('success')` with a fixed English string.
- Views: `resources/views/admin/tax_classes/*` and `resources/views/admin/tax_rates/*`.

## TaxClassController

**File:** `app/Http/Controllers/Admin/TaxClassController.php`

Permissions:

| Actions | Permission |
|---------|------------|
| `index` | `tax_class_view` |
| `create`, `store` | `tax_class_create` |
| `edit`, `update` | `tax_class_edit` |
| `delete` | `tax_class_delete` |

### `index(Request $request)`

- Non-AJAX: view `admin.tax_classes.index` with search string `q`.
- AJAX: DataTables of `TaxClass`, `withCount('rates')`, `orderByDesc('id')`.
- Search `q`: `name` or `code` `LIKE %q%`.
- Extra column `actions`: `admin.tax_classes.actions` (edit/delete, gated by `@can`).

Index columns: `DT_RowIndex`, `code`, `name`, `rates_count`, `actions`.

### `create()`

Loads all `TaxRate` ordered by `priority`, then `id`. View `admin.tax_classes.create`.

### `store(StoreTaxClassRequest $req)`

In a DB transaction:

1. `TaxClass::create($req->validated())` — only `code` / `name` persist (fillable).
2. `syncRates($class, $req)`.

Redirect: `admin.tax_classes.index`, flash `Tax class created.`

### `edit(int $id)`

`TaxClass::with('rates')->findOrFail($id)`. Same rate list as create. View `admin.tax_classes.edit`.

### `update(UpdateTaxClassRequest $req, int $id)`

Transaction: update validated fields, then `syncRates`. Redirect **back**, flash `Tax class updated.`

### `delete(int $id)`

`findOrFail` then `delete()`. Cascade/null behavior: [database.md](database.md). Redirect index, flash `Tax class deleted.`

No product-in-use check.

### `syncRates(TaxClass $class, Request $req)`

Protected. Builds a `sync()` payload from:

- `rate_ids` (array of IDs)
- `rate_sort_orders[$rateId]` (integer, default `0`)

Calls `$class->rates()->sync($sync)`. Omitting `rate_ids` syncs to an empty set (detaches all rates).

Form: `admin.tax_classes._form` — checkbox `rate_ids[]` and `rate_sort_orders[{id}]`.

## TaxRateController

**File:** `app/Http/Controllers/Admin/TaxRateController.php`

Permissions:

| Actions | Permission |
|---------|------------|
| `index` | `tax_rate_view` |
| `create`, `store` | `tax_rate_create` |
| `edit`, `update` | `tax_rate_edit` |
| `delete` | `tax_rate_delete` |

### `index(Request $request)`

- Non-AJAX: view `admin.tax_rates.index` with `q`, `country`, `active`.
- AJAX query: `TaxRate` with `classes`, `country`; order `priority`, then `id`.

Filters:

| Input | Effect |
|-------|--------|
| `q` | `name` or `code` `LIKE` |
| `country` | `country_iso2` = `strtoupper(country)` when non-empty |
| `active` | if not null/empty: `is_active` true when value is `'1'`, `1`, or `true`; otherwise false |

Computed / edited DataTables columns:

| Column | Source |
|--------|--------|
| `region` | Country name + ISO2 if `country` relation present, else `country_iso2` or `—`; appends ` / {state_iso2}` when set. Filter searches `country_iso2`, `state_iso2`, or `countries.name`. |
| `zip` | If `is_zip_range`: `{zip_from}–{zip_to}`; else `postcode` or `—`. Filter searches `postcode`, `zip_from`, `zip_to`. |
| `rate_percent` | `number_format(..., 4)` |
| `compound` / `is_active` | HTML badges (raw columns) |
| `actions` | `admin.tax_rates.actions` |

### `create()` / `store(StoreTaxRateRequest $req)`

Create view has no country dropdown; ISO2 is a text field. `TaxRate::create($req->validated())`. Redirect index, flash `Tax rate created.`

### `edit(int $id)` / `update(UpdateTaxRateRequest $req, int $id)`

`findOrFail`. Update then **back**, flash `Tax rate updated.`

### `delete(int $id)`

`findOrFail` then `delete()`. Redirect index, flash `Tax rate deleted.`

## Views (this module)

| Path | Role |
|------|------|
| `admin/tax_classes/index.blade.php` | Server-side DataTables + `q` filter |
| `admin/tax_classes/create.blade.php` / `edit.blade.php` | Wrap `_form` |
| `admin/tax_classes/_form.blade.php` | `code`, `name`, attach rates table |
| `admin/tax_classes/actions.blade.php` | Edit link; DELETE form to `admin.tax_classes.delete` |
| `admin/tax_rates/index.blade.php` | DataTables + `q` / `country` / `active` |
| `admin/tax_rates/create.blade.php` / `edit.blade.php` | Wrap `_form` |
| `admin/tax_rates/_form.blade.php` | Rate fields including zone, percent, compound, active |
| `admin/tax_rates/actions.blade.php` | Edit; DELETE to `admin.tax_rates.delete` |

Sidebar: `resources/views/layouts/components/main-sidebar.blade.php` — Tax submenu under `@canany(['tax_class_view', 'tax_rate_view'])`.

## Controllers that read tax classes (not this module)

`ProductController` loads `TaxClass::orderBy('id')->get()` for product create/edit (`tax_class_id` select).

`SettingController::hydrateDynamicSchema()` loads tax class **codes** and, if any exist, turns `tax.shipping.tax_class_code` into a select of those codes.
