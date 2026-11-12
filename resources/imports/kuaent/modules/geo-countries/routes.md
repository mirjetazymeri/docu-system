# Routes (web)

File: `routes/web.php`. Import: `App\Http\Controllers\Admin\CountryController`.

## Admin group

Middleware: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`. Prefix `admin`. Name prefix `admin.`.

Additional permission middleware is on the controller constructor (`check.permission:country_*`). See [controllers.md](controllers.md).

| Method | URI | Name | Action |
| --- | --- | --- | --- |
| GET | `admin/countries` | `admin.countries.index` | `index` |
| GET | `admin/countries/create` | `admin.countries.create` | `create` |
| POST | `admin/countries` | `admin.countries.store` | `store` |
| POST | `admin/countries/import` | `admin.countries.import` | `import` |
| GET | `admin/countries/edit/{id}` | `admin.countries.edit` | `edit` |
| PUT | `admin/countries/{id}` | `admin.countries.update` | `update` |
| POST | `admin/countries/{id}/default` | `admin.countries.default` | `setDefault` |
| DELETE | `admin/countries/{id}` | `admin.countries.delete` | `delete` |
| POST | `admin/countries/{countryId}/cities` | `admin.countries.cities.store` | `storeCity` |
| POST | `admin/countries/{countryId}/cities/import` | `admin.countries.cities.import` | `importCities` |
| PUT | `admin/countries/{countryId}/cities/{cityId}` | `admin.countries.cities.update` | `updateCity` |
| DELETE | `admin/countries/{countryId}/cities/{cityId}` | `admin.countries.cities.delete` | `deleteCity` |

`{id}` / `{countryId}` / `{cityId}` are integer primary keys (not ISO codes). `importCities` method parameter is named `$id` while the route parameter is `{countryId}`.

There is no `Route::resource` for countries.

Shipping group admin routes (`admin/shipping-groups*`) are shipping-owned; see [../shipping/routes.md](../shipping/routes.md).

## API routes

See [api.md](api.md). Defined in `routes/api.php`.
