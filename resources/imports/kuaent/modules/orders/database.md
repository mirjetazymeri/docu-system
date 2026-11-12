# Orders — database

Schema is the **sum of migrations**. There is no later “rewrite” migration; columns below are the live shape after all listed files.

## Tables owned here

### `orders`

Created in `2025_09_01_145651_create_orders_domain.php.php`, then altered.

| Column | Type / notes |
|---|---|
| `id` | bigint PK |
| `number` | string(32), unique, nullable |
| `idempotency_key` | string(191), unique, nullable — `2026_03_23_165801_add_idempotency_key_to_orders_table.php` |
| `user_id` | FK `users`, nullOnDelete |
| `sales_specialist_id` | FK `users`, nullOnDelete — `2026_06_11_120000_add_sales_specialist_and_source_to_orders_table.php` |
| `source` | string(32), nullable, indexed |
| `email` | string, nullable, indexed |
| `status` | string(32), default `pending` |
| `currency` | string(3), default `EUR` |
| `subtotal`, `discount_total`, `shipping_total`, `deposit_total`, `tax_total`, `grand_total` | decimal(12,2); `deposit_total` from `2026_07_03_100300_add_rental_fields_to_carts_and_orders.php` |
| `prices_include_tax` | boolean, nullable — `2026_07_16_160000_add_tax_snapshot_to_orders_table.php` |
| `tax_breakdown` | json, nullable |
| `coupon_code` | string(64), nullable |
| `coupon_discount` | decimal(12,2), default 0 |
| `customer_note` | text, nullable |
| `admin_note` | text, nullable — `2026_07_16_180000_add_admin_note_to_orders_table.php` |
| `external_order_sync_status` | string(32), nullable — `2026_08_04_124500_add_external_order_sync_to_orders_table.php` |
| `external_order_synced_at` | timestamp, nullable |
| `external_order_sync_error` | text, nullable |
| `external_order_sync_response` | json, nullable |
| `placed_at`, `paid_at`, `shipped_at`, `completed_at`, `canceled_at` | timestamps, nullable; `shipped_at`/`canceled_at` from `2026_04_04_100000_add_shipped_canceled_at_to_orders.php` |
| `deleted_at` | soft deletes |
| `created_at`, `updated_at` | |

Indexes from create: `(status, placed_at)`, `email`. Plus unique `number`, unique `idempotency_key`, index `source`.

**No `channel_id` column.**

Place-time number (`OrderService::makeOrderNumber`): `{Ymd}-{id padded to 6}` e.g. `20260902-000123`.

### `order_items`

| Column | Notes |
|---|---|
| `order_id` | FK cascade |
| `product_id` | FK `products`, nullOnDelete |
| `line_type` | string(10), default `sale` (`sale` \| `rental`) |
| `sku`, `name`, `qty`, `unit_price`, `row_total`, `discount`, `tax` | |
| `meta` | json |
| `rental_starts_on`, `rental_ends_on` | dates, nullable |
| `rental_periods` | unsigned int, nullable |
| `rental_period_unit` | string(10), nullable |
| `deposit_total` | decimal(12,2), default 0 |

Indexes: `order_id`, `product_id`, `sku`.

### `order_addresses`

| Column | Notes |
|---|---|
| `order_id` | FK cascade |
| `type` | enum `billing`, `shipping` |
| `data` | json |
| unique | `(order_id, type)` |

### `order_payments`

| Column | Notes |
|---|---|
| `order_id` | FK cascade |
| `payment_method_id` | FK `payment_methods`, nullOnDelete |
| `method_code`, `method_name` | snapshots |
| `status` | string(32), default `pending` |
| `amount` | decimal(12,2); **negative** for credit-note refunds |
| `currency` | string(3) |
| `reference` | string(191), nullable (PSP / cart payment reference) |
| `data` | json (gateway snapshots, `marked_by`, `credit_note_id`, …) |

Indexes: `(order_id, status)`, `reference`.

### `order_shipments`

| Column | Notes |
|---|---|
| `order_id` | FK cascade |
| `shipping_method_id` | FK `shipping_methods`, nullOnDelete — `2026_04_04_110000_add_shipping_method_id_and_unique_tracking_to_order_shipments.php` |
| `status` | default `pending` |
| `carrier_code`, `carrier_name`, `service` | |
| `amount` | decimal(12,2) |
| `tracking_number` | string(191), **unique** (NULLs allowed) |
| `tracking_url` | string(191) — admin validator allows `max:500` (longer than column) |
| `eta_text` | string(191) |
| `meta` | json (`internal_assignment`, `provider_submission`, `beki`, …) |
| `shipped_at`, `delivered_at` | timestamps |

Indexes: `(order_id, status)`; unique `tracking_number`.

Checkout `OrderService::place()` creates a shipment **without** setting `shipping_method_id` (carrier fields copied from `cart.shipping`). Admin `createShipment` also omits `shipping_method_id` unless later updated.

### `order_shipment_assignments`

`2026_04_08_000000_create_order_shipment_assignments_table.php` then `2026_04_09_130000_link_order_shipment_assignments_to_shipments.php`.

| Column | Notes |
|---|---|
| `order_id` | FK cascade |
| `order_shipment_id` | FK `order_shipments`, nullOnDelete, **unique** `order_shipment_assignments_shipment_unique` |
| `shipping_method_id` | FK `shipping_methods`, cascade on delete (original create) |
| `status` | string, default `pending` |
| `tracking_number`, `external_reference` | nullable strings |

The link migration backfills `order_shipment_id` from the latest unused shipment per order.

### `credit_notes`

`2026_07_16_170000_create_credit_notes_tables.php`

| Column | Notes |
|---|---|
| `order_id` | FK cascade |
| `number` | string(64), unique |
| `status` | default `issued` |
| `currency` | string(3) |
| `subtotal`, `discount_total`, `tax_total`, `shipping_total`, `shipping_tax`, `grand_total` | decimal(12,2) |
| `prices_include_tax` | boolean, default true |
| `tax_breakdown` | json |
| `reason` | string, nullable |
| `restocked` | boolean, default false |
| `created_by` | FK `users`, nullOnDelete |
| `order_payment_id` | FK `order_payments`, nullOnDelete |
| `issued_at` | timestamp |

Index: `(order_id, status)`.

### `credit_note_items`

| Column | Notes |
|---|---|
| `credit_note_id` | FK cascade |
| `order_item_id` | FK `order_items`, nullOnDelete |
| `sku`, `name`, `qty` | |
| `unit_gross`, `line_gross`, `line_discount`, `line_net`, `line_tax`, `line_total` | decimal(12,2) |
| `meta` | json |

## Tables this module patches (owned by other modules)

`2025_09_01_145651_create_orders_domain.php.php` adds to `carts`:

- `order_id` FK → `orders` nullOnDelete
- `ordered_at` timestamp

Rental migration also adds `deposit_total` / rental columns on `carts` and `cart_items` (checkout/rental).

## Invoice files (not a table)

PDFs are **not** stored in MySQL. Path on the `local` disk: `invoices/INV-{order.number}.pdf` → filesystem `storage/app/private/invoices/…`.
