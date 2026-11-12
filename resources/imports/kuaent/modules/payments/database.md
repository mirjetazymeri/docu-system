# Payments database

Schema after all listed migrations. No payment-specific seeders.

## `payment_methods`

Migration: `database/migrations/2025_08_29_183719_create_payment_methods_table.php.php`.

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint PK | |
| `code` | string(64) unique | Merchant-facing code |
| `name` | string(191) | |
| `driver` | string(191) | Alias or FQCN |
| `is_active` | boolean default true | |
| `allow_guest` | boolean default true | |
| `config` | json nullable | Per-driver settings |
| `min_total` | decimal(12,2) nullable | |
| `max_total` | decimal(12,2) nullable | |
| `created_at`, `updated_at` | timestamps | |

Later columns:

| Column | Migration | Type | Notes |
| --- | --- | --- | --- |
| `sort_order` | `2025_09_01_130952_add_sort_order_to_payment_methods_table.php` | integer default 0 | After `max_total` |
| `countries` | `2026_04_03_100000_add_countries_to_payment_methods_table.php` | json nullable | Excluded country codes |
| `send_invoice` | `2026_07_02_100000_add_send_invoice_to_payment_methods_table.php` | boolean default false | After `allow_guest` |

## `payment_method_channel`

Migration: `database/migrations/2026_07_07_120000_create_payment_method_channel_table.php`.

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint PK | |
| `payment_method_id` | FK `payment_methods` cascadeOnDelete | |
| `channel_id` | FK `channels` cascadeOnDelete | |
| `created_at`, `updated_at` | timestamps | |

Unique: `(payment_method_id, channel_id)`.

On migrate `up`, every existing payment method is attached to every existing channel.

## `cart_payments`

Migration: `database/migrations/2025_08_29_183917_create_cart_payments_table.php.php`.

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint PK | |
| `cart_id` | FK `carts` cascadeOnDelete | Unique (one payment per cart) |
| `payment_method_id` | FK `payment_methods` nullOnDelete, nullable | |
| `amount` | decimal(12,2) default 0 | |
| `status` | string(32) default `pending` | Indexed |
| `reference` | string(191) nullable | Gateway order id |
| `payload` | json nullable | Driver/callback snapshot |
| `created_at`, `updated_at` | timestamps | |

Comment on `status` in the migration: `pending, authorized, failed, cancelled, captured (for online payments)`.

## `order_payments`

Created in `database/migrations/2025_09_01_145651_create_orders_domain.php.php` (orders domain migration; this table is the persisted order-side payment).

| Column | Type | Notes |
| --- | --- | --- |
| `id` | bigint PK | |
| `order_id` | FK `orders` cascadeOnDelete | |
| `payment_method_id` | FK `payment_methods` nullOnDelete, nullable | |
| `method_code` | string(64) nullable | Snapshot |
| `method_name` | string(128) nullable | Snapshot |
| `status` | string(32) default `pending` | Comment: pending, authorized, captured, failed, refunded, canceled |
| `amount` | decimal(12,2) default 0 | |
| `currency` | string(3) default `EUR` | |
| `reference` | string(191) nullable | Indexed |
| `data` | json nullable | Gateway / admin snapshot |
| `created_at`, `updated_at` | timestamps | |

Index: `(order_id, status)`.

`credit_notes.order_payment_id` (nullable FK, `2026_07_16_170000_create_credit_notes_tables.php`) points at this table from the credit-note flow; it is not owned by the payments module.
