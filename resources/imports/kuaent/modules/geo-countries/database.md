# Database

## Tables owned by this module

### `countries`

Migration: `database/migrations/2026_04_10_090000_create_countries_table.php`

| Column | Definition |
| --- | --- |
| `id` | bigint PK |
| `code` | `string(2)`, unique |
| `name` | `string` (Laravel default length 255) |
| timestamps | `created_at`, `updated_at` |

Admin validation caps `name` at 120 characters; the column is wider.

### `countries.is_default`

Migration: `database/migrations/2026_06_16_100000_add_is_default_to_countries_table.php`

| Column | Definition |
| --- | --- |
| `is_default` | boolean, default `false`, after `name` |

No unique constraint. `down()` drops the column.

### `countries.shipping_group_id`

Migration: `database/migrations/2026_07_22_120100_add_shipping_group_id_to_countries_table.php`

| Column | Definition |
| --- | --- |
| `shipping_group_id` | nullable `foreignId`, after `is_default`, `constrained('shipping_groups')`, `nullOnDelete()` |

Shipping **groups** (table `shipping_groups`, model `ShippingGroup`, admin `ShippingGroupController`) are owned by [../shipping/](../shipping/). This column is the membership FK stored on `countries`.

Same migration also adds unique `(country_id, name)` on `cities` (see below). `down()` drops that unique and `dropConstrainedForeignId('shipping_group_id')`.

### `cities`

Migration: `database/migrations/2026_04_10_090100_create_cities_table.php`

| Column | Definition |
| --- | --- |
| `id` | bigint PK |
| `country_id` | FK `countries`, `cascadeOnDelete`; index `country_id` |
| `name` | `string` (default length 255) |
| timestamps | `created_at`, `updated_at` |

Unique `(country_id, name)` added later in `2026_07_22_120100_add_shipping_group_id_to_countries_table.php`. Collation / case-sensitivity of that unique is not specified in the migration.

Admin and import skip city names longer than 120 characters; the column is wider.

Deleting a country cascade-deletes its cities.

### `city_external_ids`

Migration: `database/migrations/2026_08_04_210000_create_city_external_ids_table.php`

| Column | Definition |
| --- | --- |
| `id` | bigint PK |
| `city_id` | FK `cities`, `cascadeOnDelete` |
| `provider` | `string(50)` |
| `external_id` | `string(64)` |
| `external_name` | `string`, nullable |
| timestamps | `created_at`, `updated_at` |
| unique | `(city_id, provider)` |
| index | `(provider, external_id)` |

Deleting a city cascade-deletes its external ID rows.

There is no `beki_external_id` column on `cities`. Admin field `beki_external_id` is written through `City::syncExternalId` into this table with `provider = 'beki'`.

## Seeders

### `Database\Seeders\CountrySeeder`

Path: `database/seeders/CountrySeeder.php`

`Country::updateOrCreate` by `code`:

| code | name | `is_default` |
| --- | --- | --- |
| `XK` | Kosovo | false |
| `AL` | Albania | true |
| `MK` | North Macedonia | false |
| `ME` | Montenegro | false |

Does **not** set `shipping_group_id`.

If no row has `is_default` true after the loop, calls `markAsDefault()` on the first country by `id`.

### `Database\Seeders\CitySeeder`

Path: `database/seeders/CitySeeder.php`

Skips a country code if that `Country` row is missing.

`City::updateOrCreate` on `(country_id, name)`.

**Kosovo (`XK`)** — each row may include `beki_id` / `beki_name`. Comment in seeder: names aligned with BEKI GetCities `DataTextField` where possible; `beki_id` = BEKI `DataValueField` used as AddOrder `IDCity`. When `beki_id` is non-empty, `syncExternalId(CityExternalId::PROVIDER_BEKI, …, beki_name ?? name)`.

Includes duplicate local names that share a BEKI id, for example `Prishtina` and `Pristina` both `beki_id` `1`; `Pejë` and `Peja` both `16`.

BEKI ids `1`–`37` are listed for XK municipalities (full list in the seeder file). Not repeated here.

**Albania (`AL`)**: Tirana, Durres, Vlore, Shkoder — no BEKI mapping.

**North Macedonia (`MK`)**: Skopje, Tetovo, Bitola, Kumanovo — no BEKI mapping.

**Montenegro (`ME`)**: Podgorica, Budva, Kotor, Nikšić — no BEKI mapping.

### Call order in `database/seeders/DatabaseSeeder.php`

`CountrySeeder` → `ShippingGroupSeeder` (shipping) → `CitySeeder`.

`ShippingGroupSeeder` creates groups 1–4 but does **not** write `countries.shipping_group_id`. Assignment is admin (`CountryController` / `ShippingGroupController`) or `ShippingGroupAssignmentService::applySuggestedAssignments()` (shipping).

`ModulePermissionSeeder` creates permission names listed in [README.md](README.md).

## Other tables that store ISO2 (not owned here)

These are not FKs to `countries` except where noted:

| Table / column | Relationship |
| --- | --- |
| `user_addresses.country_code` | `string(2)`; validation `exists:countries,code` on admin store/update and API address write |
| `tax_rates.country_iso2` | `char(2)` nullable; **no** FK; Eloquent `TaxRate::country()` for display |
| `payment_methods.countries` | JSON ISO2 list (exclusion); validated `Rule::in` of `Country` codes |
| `shipping_methods.countries` | JSON ISO2 list (exclusion); same pattern |
| settings `store.country` | ISO2 string in settings, default `'AL'`; used by `Country::defaultCountry()` as fallback |
