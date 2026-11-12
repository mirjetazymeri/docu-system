# Database

## `shipping_methods`

Migration: `database/migrations/2025_08_29_185054_create_shipping_methods_table.php.php`.

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `code` | string(64) unique | |
| `name` | string(191) | |
| `driver` | string(191) | Config key or FQCN |
| `is_active` | boolean, default true | |
| `allow_guest` | boolean, default true | |
| `min_total` / `max_total` | decimal(12,2) nullable | |
| `min_weight` / `max_weight` | decimal(12,3) nullable | |
| `countries` | json nullable | ISO2 list; runtime **exclusion** |
| `config` | json nullable | Driver settings |
| `sort_order` | unsigned int, default 10 | |
| timestamps | | |

## `shipping_method_channel`

Migration: `database/migrations/2026_07_07_130000_create_shipping_method_channel_table.php`.

| Column | Notes |
|---|---|
| `shipping_method_id` | FK `shipping_methods`, cascade delete |
| `channel_id` | FK `channels`, cascade delete |
| unique `(shipping_method_id, channel_id)` | |
| timestamps | |

On migrate, every existing method is attached to every existing channel.

Quotes and `setShippingMethod` require this pivot (`ShippingMethod::forChannel`).

## `shipping_groups`

Migration: `database/migrations/2026_07_22_120000_create_shipping_groups_table.php`.

| Column | Type |
|---|---|
| `id` | bigint PK |
| `number` | unsignedTinyInteger unique |
| `code` | string unique |
| `name` | string |
| `description` | text nullable |
| `sort_order` | unsignedSmallInteger default 0 |
| `is_fallback` | boolean default false |
| timestamps | |

## `countries.shipping_group_id` (geo-countries table)

Migration: `database/migrations/2026_07_22_120100_add_shipping_group_id_to_countries_table.php`.

- Nullable FK → `shipping_groups`, `nullOnDelete`.
- Same migration also adds unique `(country_id, name)` on `cities` (geo-countries side effect).

## `city_external_ids` (geo-countries; BEKI uses it)

Migration: `database/migrations/2026_08_04_210000_create_city_external_ids_table.php`.

| Column | Notes |
|---|---|
| `city_id` | FK `cities`, cascade delete |
| `provider` | string(50), e.g. `beki` |
| `external_id` | string(64) |
| `external_name` | nullable |
| unique `(city_id, provider)` | |
| index `(provider, external_id)` | |

## Orders tables this module does not own

Referenced because quotes/assignments write or read them:

| Table | Shipping-related columns |
|---|---|
| `order_shipments` | `shipping_method_id` nullable FK (`2026_04_04_110000_…`), `carrier_code`, `carrier_name`, `service`, `amount`, `tracking_number`, `eta_text`, `meta` |
| `order_shipment_assignments` | `shipping_method_id` required FK; later `order_shipment_id` unique |

Place-order does not populate `order_shipments.shipping_method_id`.

## Seeders

### `ShippingGroupSeeder` — called from `DatabaseSeeder`

| number | code | name (seeder) | is_fallback |
|---|---|---|---|
| 1 | `group_1` | Group 1 — Kosovo | false |
| 2 | `group_2` | Group 2 — Albania & North Macedonia | false |
| 3 | `group_3` | Group 3 — Montenegro & Europe | false |
| 4 | `group_4` | Group 4 — Worldwide | **true** |

`CountrySeeder` does **not** set `shipping_group_id`. Assignment is admin “Apply suggested” or per-country/group forms.

### `ShippingMethodSeeder` — **not** called from `DatabaseSeeder`

Creates (if run manually) three methods with `config` = `config("shipping.drivers.{driver}.defaults")`, synced to all channels:

| code | driver | sort_order |
|---|---|---|
| `free` | `free` | 5 |
| `dhl_express` | `dhl_express` | 10 |
| `beki_post_api_standard` | `beki_post_api_standard` | 55 |

Does **not** seed `group_rate` / `group_rate_express`. Those methods exist only if created in admin (or another process).

BEKI defaults include `frontend_visible: false`, so a seeded BEKI method is omitted from storefront quote lists until that flag is turned on.

### `CitySeeder` (geo-countries)

Writes Kosovo city names plus `CityExternalId` provider `beki` (`DataValueField` as `external_id`). Used by BEKI AddOrder city resolution.

## Permissions seed

`ModulePermissionSeeder` module **Shipping Methods**: `shipping_method_view`, `shipping_method_create`, `shipping_method_edit`, `shipping_method_delete`.

There is no separate “shipping groups” module. Group screens use **Countries** permissions (`country_view`, `country_edit`).
