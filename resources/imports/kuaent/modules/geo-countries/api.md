# API

## Group

File: `routes/api.php`.

```php
Route::prefix('v1')->middleware(['channel.api', 'locale.api'])->group(...)
```

Default Laravel API prefix: `api`. Channel is resolved **before** locale. These two endpoints do not read `currentChannel` or locale; they return the same catalog regardless.

| Method | URI | Controller | Auth |
| --- | --- | --- | --- |
| GET | `/api/v1/countries` | `App\Http\Controllers\Api\V1\CountryController@index` | none (public `v1` group) |
| GET | `/api/v1/countries/{code}/cities` | `App\Http\Controllers\Api\V1\CountryController@cities` | none |

No extra throttle on these routes. No OpenAPI annotations on `CountryController`.

`postman_collection.json` includes **List Countries** (`{{base_url}}/api/v1/countries`) and **List Cities for Country** (`{{base_url}}/api/v1/countries/{{country_code}}/cities`). Descriptions there match the JSON shapes below.

Sales-specialist create-order UI calls the same paths via `apiFetch('/countries')` and `apiFetch('/countries/' + countryCode + '/cities')` in `resources/views/sales-specialist/orders/_create_scripts.blade.php`.

## `GET /api/v1/countries`

`index()`:

1. `Country::query()->orderByDesc('is_default')->orderBy('name')->get(['code', 'name', 'is_default'])`.
2. `$default = Country::defaultCountry()` (see [models.md](models.md)).
3. JSON (no `success` wrapper):

```json
{
  "data": [
    {
      "code": "AL",
      "name": "Albania",
      "is_default": true
    }
  ],
  "default": {
    "code": "AL",
    "name": "Albania",
    "is_default": true
  }
}
```

`default` is `null` when `defaultCountry()` returns `null`.

`presentCountry`: `code` uppercased, `name` string-cast, `is_default` bool.

Does **not** include `id`, `shipping_group_id`, or cities.

## `GET /api/v1/countries/{code}/cities`

`cities(string $code)`:

- Lookup `Country` where `code` = `strtoupper(trim($code))`.
- Missing country: `firstOrFail()` → HTTP 404 (Laravel model not found).
- Cities: `$country->cities()->orderBy('name')->get(['id', 'name'])`.

JSON:

```json
{
  "data": {
    "country": {
      "code": "XK",
      "name": "Kosovo",
      "is_default": false
    },
    "cities": [
      {
        "id": 1,
        "name": "Prishtina"
      }
    ]
  }
}
```

Does **not** include `CityExternalId` / BEKI ids. Empty city list is `"cities": []`, not 404.

## What this API does not do

- No create/update/delete.
- No shipping group in the payload (groups affect quotes server-side; see [../shipping/](../shipping/)).
- Checkout `SetAddressesRequest` validates `billing.country` / `shipping.country` as `string|size:2` only — **not** `exists:countries,code`. Saved-address APIs do use `exists:countries,code` (see [dependencies.md](dependencies.md)).
