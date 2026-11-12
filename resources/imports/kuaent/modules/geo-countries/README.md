# Geo / countries

Country and city catalog for `C:\laragon\www\TestDocu-admin`: admin CRUD, storefront JSON API, CountriesNow import, and per-city external IDs (BEKI).

This module is not a Composer package. Types live under `App\`.

## Purpose

- Persist **countries** (`App\Models\Country`) with ISO2 `code`, `name`, exclusive `is_default`, and optional `shipping_group_id`.
- Persist **cities** (`App\Models\City`) per country, unique on `(country_id, name)`.
- Persist **external city IDs** (`App\Models\CityExternalId`) keyed by `provider` (admin writes `beki`).
- Import countries/cities from the public CountriesNow HTTP API (`App\Services\Geo\CountriesNowImportService`).
- Expose `GET /api/v1/countries` and `GET /api/v1/countries/{code}/cities` for storefront and sales-specialist address UIs.

## What this module does not own

| Concern | Owner |
| --- | --- |
| `ShippingGroup` model, seeder, admin CRUD, suggested geography assignment | **shipping** — see [../shipping/](../shipping/) |
| Checkout address payloads, cart `billing`/`shipping` JSON | **cart-checkout** |
| User address book rows (`user_addresses.country_code`) | addresses / users (admin `UserAddressController`, API `AddressController`) |
| Tax rate `country_iso2` (no FK) | **tax** |
| Payment/shipping method JSON `countries` exclusion lists | **payments** / **shipping** |
| Setting key `store.country` | settings (`config/settings.php`) |

This module **does** store `countries.shipping_group_id` and the country admin fields for it. Shipping groups themselves are shipping-owned.

## Owned vs shared files

Owned by this module:

| Path | Role |
| --- | --- |
| `app/Models/Country.php` | Country Eloquent model |
| `app/Models/City.php` | City Eloquent model |
| `app/Models/CityExternalId.php` | External ID mappings (`provider` + `external_id`) |
| `app/Services/Geo/CountriesNowImportService.php` | CountriesNow HTTP import + Europe ISO2 list |
| `app/Http/Controllers/Admin/CountryController.php` | Admin country/city CRUD + import |
| `app/Http/Controllers/Api/V1/CountryController.php` | Storefront country/city JSON |
| `database/seeders/CountrySeeder.php` | XK, AL, MK, ME; AL default |
| `database/seeders/CitySeeder.php` | Seed cities; BEKI IDs for Kosovo |
| `resources/views/admin/countries/` | Admin Blade UI |

Shared files that contain this module’s wiring (do not treat the whole file as this module):

| Path | What this module uses |
| --- | --- |
| `routes/web.php` | Admin `countries*` routes |
| `routes/api.php` | `GET countries`, `GET countries/{code}/cities` |
| `database/seeders/DatabaseSeeder.php` | Calls `CountrySeeder` then `CitySeeder` (groups in between) |
| `database/seeders/ModulePermissionSeeder.php` | Module `Countries` → `country_*` |
| `resources/views/layouts/components/main-sidebar.blade.php` | System configs → countries/cities |

## Docs in this folder

| File | Contents |
| --- | --- |
| [models.md](models.md) | `Country`, `City`, `CityExternalId` |
| [database.md](database.md) | Tables, migrations, seeders |
| [controllers.md](controllers.md) | Admin + API controllers, views, permissions |
| [services.md](services.md) | `CountriesNowImportService` (client, timeouts, failures) |
| [routes.md](routes.md) | Admin web routes |
| [api.md](api.md) | Storefront `GET` endpoints |
| [requests-validation.md](requests-validation.md) | Inline validation (no Form Request classes) |
| [dependencies.md](dependencies.md) | Incoming/outgoing references from code |
| [tests.md](tests.md) | Test inventory |

## Permissions

Seeded in `database/seeders/ModulePermissionSeeder.php` as `{resource}_{action}` for module display name `Countries`:

| Permission | Admin `CountryController` methods |
| --- | --- |
| `country_view` | `index` |
| `country_create` | `create`, `store`, `storeCity`, `import`, `importCities` |
| `country_edit` | `edit`, `update`, `updateCity`, `setDefault` |
| `country_delete` | `delete`, `deleteCity` |

The same `country_view` / `country_edit` names are also applied on **shipping**’s `App\Http\Controllers\Admin\ShippingGroupController` (not owned here). See [../shipping/](../shipping/).

## Environment

No environment variable names are read by this module. CountriesNow base URL is a class constant, not `config()` / `.env`. See [services.md](services.md).

`Country::defaultCountry()` may read setting `store.country` (settings module; schema default `'AL'` in `config/settings.php`). That is not a CountriesNow env var.
