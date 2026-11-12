# Database

## Tables owned by this module

### `channels`

Migration: `database/migrations/2026_03_17_153207_create_channels_table.php`

| Column | Definition |
| --- | --- |
| `id` | bigint PK |
| `name` | string |
| `code` | string, unique |
| `domain` | string, nullable |
| `timestamps` | `created_at`, `updated_at` |

### `locales`

Migration: `database/migrations/2026_02_22_000000_create_locales_table.php`

| Column | Definition |
| --- | --- |
| `id` | bigint PK |
| `code` | string, unique |
| `name` | string |
| `flag` | string, nullable |
| `is_active` | boolean, default `true` |
| `is_default` | boolean, default `false` |
| `timestamps` | `created_at`, `updated_at` |

### `channel_user`

Migration: `database/migrations/2026_03_17_153753_create_channel_user_table.php`

| Column | Definition |
| --- | --- |
| `id` | bigint PK |
| `user_id` | FK `users`, `cascadeOnDelete` |
| `channel_id` | FK `channels`, `cascadeOnDelete` |
| `timestamps` | `created_at`, `updated_at` |

No unique index on `(user_id, channel_id)`.

### `channel_locale`

Migration: `database/migrations/2026_06_09_120000_create_channel_locale_table.php`

| Column | Definition |
| --- | --- |
| `id` | bigint PK |
| `channel_id` | FK `channels`, `cascadeOnDelete` |
| `locale_id` | FK `locales`, `cascadeOnDelete` |
| `is_default` | boolean, default `false` |
| `timestamps` | `created_at`, `updated_at` |
| unique | `(channel_id, locale_id)` |

On `up()`, after create, the migration loads active locales and `sync`s them onto **every existing** `Channel`, marking the global default locale (or first active) as pivot `is_default`. If no active locales exist at migrate time, it returns without inserting rows.

## User columns

### `users.locale`

Migration: `database/migrations/2025_08_09_161533_add_locale_to_users_table.php`

- `string('locale', 5)->default('en')`
- `down()` drops the column

### `users.current_channel_id`

Migration: `database/migrations/2026_03_17_160601_add_current_channel_id_to_users_table.php`

- `foreignId('current_channel_id')->nullable()->after('id')->constrained('channels')->nullOnDelete()`
- `down()` is empty (does not drop the column)

## Seeders

### `Database\Seeders\LocaleSeeder`

Path: `database/seeders/LocaleSeeder.php`

`updateOrCreate` by `code`:

| code | name | flag | is_active | is_default |
| --- | --- | --- | --- | --- |
| `sq` | Shqip | `albania_flag.jpg` | true | true |
| `en` | English | `us_flag.jpg` | true | false |

### `Database\Seeders\ChannelSeeder`

Path: `database/seeders/ChannelSeeder.php`

1. `Channel::updateOrCreate(['code' => 'default'], ['name' => 'Default', 'domain' => null])`
2. `app(ChannelLocaleService::class)->attachAllActiveLocales($channel)`
3. For every `User`: `channels()->syncWithoutDetaching([$channel->id])`; if `current_channel_id` is empty, set it to this channel

`attachAllActiveLocales` no-ops when there are no active locales.

### Call order in `database/seeders/DatabaseSeeder.php`

`ChannelSeeder` is called **before** `LocaleSeeder`. On a first seed with empty `locales`, step 2 of `ChannelSeeder` attaches nothing. HTTP fallback in `ChannelLocaleService` then uses `config('locales')` / active locales once they exist.

`ModulePermissionSeeder` (after locale seed) creates permission names listed in [README.md](README.md).

## Other tables that FK `channels` (not owned here)

These migrations reference `channels.id`. They belong to other modules; listed because deleting a channel cascades or nulls depending on the FK:

| Migration | Table / column | On delete |
| --- | --- | --- |
| `2026_03_17_153619_add_channel_id_to_settings_table.php` | `settings.channel_id` nullable | cascade |
| `2026_03_17_172207_add_unique_kwy_to_settings_table.php` | unique `(key, channel_id)` | — |
| `2026_03_17_183545_create_category_channel_table.php` | `category_channel` | cascade |
| `2026_03_18_105956_create_attribute_channel_table.php` | `attribute_channel` | cascade |
| `2026_03_18_142555_create_product_channel_table.php` | `product_channel` | cascade |
| `2026_03_18_164958_create_product_prices_table.php` | `product_prices.channel_id` | cascade |
| `2026_03_18_165017_create_product_price_histories_table.php` | `product_price_histories.channel_id` | cascade |
| `2026_03_19_085829_add_channel_to_catalog_rules.php` | `catalog_rules.channel_id` nullable | cascade |
| `2026_03_19_085854_add_channel_to_catalog_rule_product_prices.php` | `catalog_rule_product_prices.channel_id` nullable indexed; unique `(product_id, user_group_id, channel_id)` | no `constrained()` in that migration |
| `2026_03_19_102544_create_page_channel_table.php` | `page_channel` | cascade |
| `2026_03_19_102819_create_page_channel_contents_table.php` | `page_channel_contents.channel_id` | cascade |
| `2026_03_19_121130_create_widget_channel_table.php` | `widget_channel` | cascade |
| `2026_03_23_163338_add_channel_id_to_carts_table.php` | `carts.channel_id` | FK `channels.id`, cascadeOnDelete |
| `2026_03_04_160717_create_product_projections_table.php` | `product_projections.channel_id` | unsigned bigint (no FK in that file) |
| `2026_04_09_120000_create_newsletter_subscribers_table.php` | `newsletter_subscribers.channel_id` | cascade |
| `2026_04_09_180000_create_storefront_events_table.php` | `storefront_events.channel_id` nullable | nullOnDelete |
| `2026_04_09_183000_create_storefront_consents_table.php` | `storefront_consents.channel_id` nullable | nullOnDelete |
| `2026_05_10_172102_create_wishlists_table.php` | `wishlists.channel_id` nullable | nullOnDelete |
| `2026_05_10_174236_create_compares_table.php` | `compares.channel_id` nullable | nullOnDelete |
| `2026_05_26_100000_create_promotions_tables.php` | `promotions.channel_id` nullable | nullOnDelete |
| `2026_06_01_100000_create_product_synonyms_table.php` | `product_synonyms.channel_id` | cascadeOnDelete |
| `2026_06_05_120000_add_channel_id_to_coupons_table.php` | `coupons.channel_id` nullable | cascadeOnDelete |
| `2026_07_03_100100_create_product_rental_prices_table.php` | `product_rental_prices.channel_id` | cascade |
| `2026_07_03_100200_create_rental_reservations_table.php` | `rental_reservations.channel_id` | cascade |
| `2026_07_07_120000_create_payment_method_channel_table.php` | `payment_method_channel` | cascade |
| `2026_07_07_130000_create_shipping_method_channel_table.php` | `shipping_method_channel` | cascade |
