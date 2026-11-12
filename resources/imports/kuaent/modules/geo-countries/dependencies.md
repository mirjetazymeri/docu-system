# Dependencies

Only references found in code. This module is not a Laravel package.

## Outgoing (this module depends on)

### Framework / packages

| Symbol | Used by |
| --- | --- |
| `Illuminate\Http\Request`, `Illuminate\Support\Str`, `Illuminate\Validation\Rule`, `Illuminate\Support\Facades\Http`, `Illuminate\Support\Facades\DB` | admin controller, `CountriesNowImportService` |
| `Yajra\DataTables\Facades\DataTables` | admin `CountryController::index` |
| `app/Http/Controllers/Controller.php` | both country controllers |
| `check.permission` → `App\Http\Middleware\AutoAuthorizePermission` | admin controller |
| `auth`, `verified.if-required`, `set.user.locale`, `role:Admin` | admin route group in `routes/web.php` |
| `channel.api`, `locale.api` | API `v1` group (applied, not consumed by `CountryController` logic) |

### App types

| Symbol | Path | Used by |
| --- | --- | --- |
| `App\Models\Country` | `app/Models/Country.php` | controllers, import service, seeders |
| `App\Models\City` | `app/Models/City.php` | admin controller, import service, `CitySeeder` |
| `App\Models\CityExternalId` | `app/Models/CityExternalId.php` | admin controller, `CitySeeder` |
| `App\Models\ShippingGroup` | `app/Models/ShippingGroup.php` | admin `CountryController` list/filter/form (`ShippingGroup::query()`) |
| `setting()` | `app/helpers.php` → settings | `Country::defaultCountry()` key `store.country` |

CountriesNow: HTTP only; see [services.md](services.md). No Composer package.

## Incoming (other code depends on this module)

### Shipping — [../shipping/](../shipping/)

| Use | Where |
| --- | --- |
| `countries.shipping_group_id` / `Country::shippingGroup()` | `GroupRateShippingDriver::resolveGroup`, `ShippingGroupAssignmentService`, `ShippingGroupController` |
| `CountriesNowImportService::europeanIsoCodes()` | suggested group assignment (XK → 1, AL/MK → 2, Europe list → 3, else 4) |
| `City` + `CityExternalId::PROVIDER_BEKI` | `BekiPostApiDriver` local city → AddOrder `IDCity` |
| Admin fields `shipping_group_id` and `beki_external_id` | this module’s `CountryController` / views |
| Permissions `country_view` / `country_edit` | `ShippingGroupController` |

Storefront country JSON does not include the group. Quote path reads the FK on the server.

### Cart / checkout

`App\Support\CheckoutAddressFields` and `SetAddressesRequest` accept ISO2 `country` strings without `exists:countries`. Bundle checkout test uses address `country` => `'XK'` as JSON, not the `Country` model.

`GroupRateShippingDriver` reads `cart.shipping` / `cart.billing` `country_code` or `country` and loads `Country` by `code`.

### Addresses

- `App\Http\Controllers\Admin\UserAddressController` — `Country::query()->pluck('name', 'code')` for forms and DataTables labels; `exists:countries,code` on store/update.
- `App\Http\Controllers\Api\V1\AddressController::countryNameMap()` — `pluck('name', 'code')`; write validation `exists:countries,code`.

### Payments

- `StorePaymentMethodRequest` / `UpdatePaymentMethodRequest` — `countries.*` must be in `Country` codes.
- `PaymentMethodController` (admin) and payment method form — `Country::query()->pluck('name', 'code')` for exclusion UI.
- API `PaymentMethodController` reads `$method->countries` as an exclusion list (ISO2 on the method row, not this module’s table).

### Tax

- `TaxRate::country()` belongsTo `Country` on `country_iso2` / `code` (no FK).
- `TaxRateController::index` eager-loads `country` for region labels.
- `TaxSettings` uses setting `store.country` as origin fallback (not `Country::defaultCountry()`).

### Sales specialist

`App\Http\Controllers\SalesSpecialist\OrderController` passes `defaultCountry` from `setting('store.country', 'AL')` (setting, not necessarily `countries.is_default`). Blade scripts load `/countries` and `/countries/{code}/cities`.

### Invoice

`App\Services\InvoiceService` uses `setting('store.country', '')` as a string on the invoice payload, not the `Country` model.

### Seed / permissions / UI

- `database/seeders/DatabaseSeeder.php` — `CountrySeeder`, `CitySeeder`
- `database/seeders/ModulePermissionSeeder.php` — module `Countries`
- `resources/views/layouts/components/main-sidebar.blade.php`
- `lang/{en,sq}/translations.php` keys under `admin.countries`

### Direction (shipping FK)

```
geo-countries  ──countries.shipping_group_id, CityExternalId──►  shipping
shipping       ──ShippingGroup CRUD, applySuggested──────────►  countries.shipping_group_id
```
