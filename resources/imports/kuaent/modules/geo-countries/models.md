# Models

No factories under `database/factories/`. No observers registered for these models in `App\Providers\EventServiceProvider`.

## `App\Models\Country`

Path: `app/Models/Country.php`

Table: `countries` (default). Extends `Illuminate\Database\Eloquent\Model`. No `$hidden`, no `$appends`.

Fillable: `code`, `name`, `is_default`, `shipping_group_id`.

Casts: `is_default` → boolean.

### Relations

| Method | Type | Related | FK |
| --- | --- | --- | --- |
| `cities()` | `hasMany(City::class)` | `App\Models\City` | `cities.country_id` |
| `shippingGroup()` | `belongsTo(ShippingGroup::class)` | `App\Models\ShippingGroup` (shipping module) | `countries.shipping_group_id` |

Inverse on shipping: `ShippingGroup::countries()` is `hasMany(Country::class)`.

### `Country::defaultCountry(): ?self`

Resolution order:

1. First row with `is_default = true`.
2. Else `setting('store.country', '')`, trimmed and uppercased; lookup `countries.code` if non-empty.
3. Else first country ordered by `name`.
4. Else `null`.

Called from `App\Http\Controllers\Api\V1\CountryController::index` only (in this repository).

### `markAsDefault(): void`

Sets `is_default = false` on every other row (`whereKeyNot($this->id)`), then `$this->update(['is_default' => true])`. There is no unique index on `is_default`; exclusivity is application-level.

`persistCountry` in the admin controller calls `markAsDefault()` only when the saved row’s `is_default` is true. Unchecking default on the current default does not promote another country.

## `App\Models\City`

Path: `app/Models/City.php`

Table: `cities`. Extends `Illuminate\Database\Eloquent\Model`.

Fillable: `country_id`, `name`.

### Relations

| Method | Type | Related |
| --- | --- | --- |
| `country()` | `belongsTo(Country::class)` | `App\Models\Country` |
| `externalIds()` | `hasMany(CityExternalId::class)` | `App\Models\CityExternalId` |

### `externalIdFor(string $provider): ?string`

Returns the trimmed `external_id` for that `provider`, or `null` if missing or blank. Uses the loaded `externalIds` relation when present; otherwise queries.

### `syncExternalId(string $provider, ?string $externalId, ?string $externalName = null): void`

- Blank/null `$externalId` (after trim): deletes all `city_external_ids` rows for that `provider`.
- Otherwise `updateOrCreate` on `['provider' => $provider]` with `external_id` and optional trimmed `external_name`.

Admin country city forms pass `CityExternalId::PROVIDER_BEKI` and form field `beki_external_id`. `$externalName` is omitted there (stays `null` unless a caller supplies it). `CitySeeder` passes BEKI display names as `$externalName`.

## `App\Models\CityExternalId`

Path: `app/Models/CityExternalId.php`

Table: `city_external_ids`.

Constant: `PROVIDER_BEKI = 'beki'`. No other provider constants are defined on this class.

Fillable: `city_id`, `provider`, `external_id`, `external_name`.

### Relations

| Method | Type | Related |
| --- | --- | --- |
| `city()` | `belongsTo(City::class)` | `App\Models\City` |

Unique `(city_id, provider)` is enforced in the migration, not in the model.

## Related models (not owned)

`App\Models\ShippingGroup` — see [../shipping/models.md](../shipping/models.md). This module only declares `Country::shippingGroup()`.

`App\Models\TaxRate::country()` is `belongsTo(Country::class, 'country_iso2', 'code')` with **no** database FK. Tax-owned.
