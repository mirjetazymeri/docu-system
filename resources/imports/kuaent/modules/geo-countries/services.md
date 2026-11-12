# Services

## `App\Services\Geo\CountriesNowImportService`

Path: `app/Services/Geo/CountriesNowImportService.php`

Not registered as a singleton in a service provider; resolved from the container as the default class binding.

Uses `App\Models\Country`, `App\Models\City`, `Illuminate\Support\Facades\DB`, `Illuminate\Support\Facades\Http`.

### Client

Laravel HTTP client (`Http` facade). There is no dedicated CountriesNow SDK in `composer.json`.

### Config

None. No `config/geo.php` (or similar) and no `config('…')` reads in this class.

Base URL is a class constant:

`CountriesNowImportService::BASE_URL = 'https://countriesnow.space/api/v0.1'`

### Environment variable names

None. No `env()` / `$_ENV` usage in this class. No `COUNTRIESNOW_*` (or other CountriesNow) names appear in `.env.example` or `config/`.

### Authentication

None in code. Requests use `acceptJson()` only. No tokens, headers, basic auth, or query API keys.

### Called from

| Caller | Method |
| --- | --- |
| `App\Http\Controllers\Admin\CountryController::import` | `import(bool $withCities = true)` |
| `App\Http\Controllers\Admin\CountryController::importCities` | `importCitiesForCountry(Country $country)` |
| `App\Services\Shipping\ShippingGroupAssignmentService::applySuggestedAssignments` | static `europeanIsoCodes()` only (no HTTP) |

### Timeout / retry

Timeouts are set per request. **Retry is not implemented** (`Http::retry` is not used in this class; a repo-wide search found no `Http::retry` / `->retry(`).

| HTTP call | Method | URL | Timeout (seconds) |
| --- | --- | --- | --- |
| ISO list | GET | `{BASE_URL}/countries/iso` | 60 |
| Countries + city names | GET | `{BASE_URL}/countries` | 120 |
| Cities for one country name | POST body `['country' => $alias]`; `acceptJson()` | `{BASE_URL}/countries/cities` | 60 |

### `europeanIsoCodes(): array`

Static ISO2 list used as “Europe” for shipping suggested group assignment (includes `XK`). Not used by the import HTTP path. Shipping documents the group numbers in [../shipping/](../shipping/).

### `isoOverrides` (protected)

Name → ISO2 map applied after the API ISO list (normalized names). Used so CountriesNow country names that miss or mismatch ISO2 still import (e.g. Kosovo → `XK`). Full map is in the PHP file; not duplicated here.

### `import(bool $withCities = true): array`

1. `fetchIsoList()` then `fetchCountriesWithCities()`.
2. Build `$isoByName` from API rows (`name` + `Iso2` or `iso2`, length 2). Merge `$isoOverrides` (overrides win).
3. `DB::transaction`: for each API country row:
   - Empty `country` name → continue.
   - No ISO2 → append name to `$stats['skipped']`, continue.
   - Existing `Country` by `code`: update `name` only (`countries_updated`). Does not change `is_default` or `shipping_group_id`.
   - Else `Country::create` with `code`, `name`, `is_default` => false (`countries_created`). No `shipping_group_id`.
   - If `$withCities`: unique trimmed city strings; skip empty or `mb_strlen` > 120 or case-insensitive name already on that country (`cities_skipped`); bulk `City::query()->insert` in chunks of 500 (`cities_created`).
4. Always `Country::updateOrCreate(['code' => 'XK'], ['name' => 'Kosovo'])` inside the same transaction (comment: ensure Kosovo exists if API naming drifts). Does not set `is_default`.

Return keys: `countries_created`, `countries_updated`, `cities_created`, `cities_skipped`, `skipped` (list of country names without ISO mapping).

Does not write `city_external_ids`.

### `importCitiesForCountry(Country $country): array`

Names to try: `$country->name` plus override keys whose ISO2 equals `$country->code`.

For each alias: POST `{BASE_URL}/countries/cities` with `['country' => $alias]`.

- HTTP not successful → set `$lastError` to `CountriesNow HTTP {status} for {alias}`, try next alias.
- JSON `error` truthy → `$lastError` from JSON `msg` if present, try next.
- `data` non-empty array → stop.
- If all aliases fail or `data` stays empty → `throw new RuntimeException($lastError)` (initial message `CountriesNow could not return cities for {name}`).

Then insert-or-skip like import (no transaction wrapper). Return `created`, `skipped`.

### `fetchIsoList()` / `fetchCountriesWithCities()` (protected)

GET as in the timeout table. If `!$response->successful()` **or** `$response->json('error')` is truthy:

- ISO: `RuntimeException` `'Failed to fetch country ISO list from CountriesNow API.'`
- Countries: `RuntimeException` `'Failed to fetch countries/cities from CountriesNow API.'`

Returns `(array) $response->json('data')` (empty array if missing).

### `normalizeName(string $name): string` (protected)

Lowercase trim, normalize quotes, collapse whitespace. Used for ISO map keys.

### Failure handling (summary)

| Situation | Behavior |
| --- | --- |
| ISO or countries GET HTTP error or JSON `error` | `RuntimeException`; admin `import` catches `Throwable`, flash `Import failed: …` |
| Country name with no ISO2 | Listed in `skipped`; other countries still imported |
| City empty / > 120 chars / duplicate (case-insensitive) | Counted in skip stats; not inserted |
| Per-country cities POST failure | Next alias; if none succeed, `RuntimeException`; admin `importCities` flash `City import failed: …` |
| DB error inside `import()` | Transaction rolls back; exception bubbles to controller |

No logging facade calls in this class. No jobs/queues.
