# Controllers

Base class: `App\Http\Controllers\Controller` (`AuthorizesRequests`, `DispatchesJobs`, `ValidatesRequests`).

There are no `app/Http/Requests` classes for countries or cities. Validation is inline; see [requests-validation.md](requests-validation.md).

DataTables: `Yajra\DataTables\Facades\DataTables`.

## `App\Http\Controllers\Admin\CountryController`

Path: `app/Http/Controllers/Admin/CountryController.php`

Constructor injects nothing. `CountriesNowImportService` is method-injected on `import` and `importCities`.

Middleware (`check.permission` → `App\Http\Middleware\AutoAuthorizePermission`):

| Permission | Methods |
| --- | --- |
| `country_view` | `index` |
| `country_create` | `create`, `store`, `storeCity`, `import`, `importCities` |
| `country_edit` | `edit`, `update`, `updateCity`, `setDefault` |
| `country_delete` | `delete`, `deleteCity` |

### `index(Request $request)`

AJAX: `Country::query()->with('shippingGroup')`, optional `q` (LIKE on `code` or `name`), optional `shipping_group_id` (integer; `0` treated as no filter), `withCount('cities')`, order `is_default` desc then `name`. DataTables columns: uppercased `code`, HTML `shipping_group` (`G{number}` badge + escaped name, or muted `Unassigned`), `is_default` (badge or `admin.countries.set_default` form if `country_edit`), `actions` view `admin.countries.actions`. Raw HTML: `shipping_group`, `is_default`, `actions`.

Non-AJAX: `view('admin.countries.index')` with `$q` and `$shippingGroups` (`ShippingGroup` ordered `sort_order` then `number`).

### `import(Request $request, CountriesNowImportService $importer)`

Validates `with_cities` `sometimes|boolean`. Calls `$importer->import($request->boolean('with_cities', true))`.

If the checkbox is omitted from the POST body, Laravel `boolean('with_cities', true)` uses default **true** (cities are imported). The index form checkbox is named `with_cities` value `1`, checked by default.

On `Throwable`: `back()` with flash `error` `Import failed: ` + `$e->getMessage()`.

On success: flash `success` with created/updated country counts, cities created, city rows skipped; if `skipped` (no ISO mapping) is non-empty, appends how many countries were skipped.

Redirect: `back()`.

### `importCities(int $id, CountriesNowImportService $importer)`

Route parameter is `{countryId}`; method argument is `$id`. `Country::findOrFail($id)` then `$importer->importCitiesForCountry($country)`.

On `Throwable`: `back()` with `City import failed: ` + message.

On success: flash `Imported {created} cities for {name} ({skipped} skipped).`

### `create()`

`$shippingGroups` same query as index. `view('admin.countries.create')`.

### `store(Request $request)`

`validateCountry` + `persistCountry`. Redirect `admin.countries.index` with `Country created.`

### `edit(int $id)`

`Country::with(['cities' => orderBy name, with externalIds, 'shippingGroup'])->findOrFail`. Same `$shippingGroups`. `view('admin.countries.edit')`.

### `update(Request $request, int $id)`

`findOrFail`, `validateCountry(..., $country->id)`, `persistCountry($data, $country)`. `back()` with `Country updated.`

### `delete(int $id)`

`Country::findOrFail($id)->delete()` (cascades cities and external IDs). Redirect index with `Country deleted.` No guard against deleting the default country.

### `storeCity(Request $request, int $countryId)`

`findOrFail` country. Validates city `name` unique per `country_id`, optional `beki_external_id`. Creates city with trimmed name. `syncExternalId(CityExternalId::PROVIDER_BEKI, beki_external_id)`. `back()` `City added.`

### `updateCity(Request $request, int $countryId, int $cityId)`

City scoped `where('country_id', $country->id)->findOrFail($cityId)`. Same validation with unique ignore. Updates name; syncs BEKI id (blank deletes mapping). `back()` `City updated.`

### `deleteCity(int $countryId, int $cityId)`

Same city scoping. `$city->delete()`. `back()` `City deleted.`

### `setDefault(int $id)`

`findOrFail`, `markAsDefault()`. `back()` `{name} is now the default country.`

### `validateCountry` / `persistCountry` (protected)

See [requests-validation.md](requests-validation.md). Create vs update: `Country::create` or `$country->update`. If `$country->is_default`, `markAsDefault()`.

### Views

| View | Path |
| --- | --- |
| index | `resources/views/admin/countries/index.blade.php` |
| create | `resources/views/admin/countries/create.blade.php` |
| edit | `resources/views/admin/countries/edit.blade.php` |
| form | `resources/views/admin/countries/_form.blade.php` |
| actions | `resources/views/admin/countries/actions.blade.php` |
| set default | `resources/views/admin/countries/set_default.blade.php` |

`_form` fields: `code` (maxlength 2), `name`, checkbox `is_default` value `1`, select `shipping_group_id` (empty = Unassigned, options `G{number} — {name}`).

Edit page also: CitiesNow import form (`admin.countries.cities.import`), add-city form (`name`, `beki_external_id`), per-city update/delete.

Index: CountriesNow import form (`admin.countries.import`), link `admin.shipping_groups.index` behind `country_view`, create behind `country_create`. Filters `q` and `shipping_group_id` posted into DataTables ajax `data`.

## `App\Http\Controllers\Api\V1\CountryController`

Path: `app/Http/Controllers/Api/V1/CountryController.php`

No constructor middleware. Auth: none (public `v1` group). See [api.md](api.md).

Methods: `index()`, `cities(string $code)`, `presentCountry(Country $country)` (protected).

No OpenAPI annotations on this class.

## Admin navigation

`resources/views/layouts/components/main-sidebar.blade.php`:

- System settings → System configs: `route('admin.countries.index')` behind `@can('country_view')` (label `translations.admin.sidebar.countries_cities`).
- Shipping methods submenu: shipping **groups** link uses `@can('country_view')` but is shipping-owned (`admin.shipping_groups.index`).
