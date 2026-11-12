# Rental — database

Schema from migrations under `database/migrations`. No seeders or factories for rental tables.

## Owned tables

### `product_rental_prices`

`database/migrations/2026_07_03_100100_create_product_rental_prices_table.php`

| Column | Notes |
|---|---|
| `id` | PK |
| `product_id` | FK `products`, `cascadeOnDelete` |
| `channel_id` | FK `channels`, `cascadeOnDelete` |
| `period_unit` | string(10), default `day` — comment: `day \| week \| month` |
| `rate` | decimal(12,2) — price per period |
| `deposit` | decimal(12,2), nullable — optional deposit |
| `min_period` | unsigned int, default `1` |
| `max_period` | unsigned int, nullable |
| `currency` | string(3), default `EUR` |
| `created_at`, `updated_at` | |

Unique `(product_id, channel_id)`. Index on `channel_id`.

### `rental_reservations`

`database/migrations/2026_07_03_100200_create_rental_reservations_table.php`

| Column | Notes |
|---|---|
| `id` | PK |
| `product_id` | FK `products`, `cascadeOnDelete` |
| `channel_id` | FK `channels`, `cascadeOnDelete` |
| `cart_id` | nullable FK `carts`, `nullOnDelete` |
| `order_id` | nullable FK `orders`, `nullOnDelete` |
| `cart_item_id` | unsigned bigint, nullable — **no FK** |
| `order_item_id` | unsigned bigint, nullable — **no FK** |
| `qty` | unsigned int, default `1` |
| `starts_on`, `ends_on` | date |
| `status` | string(20), default `held` — comment: `held \| confirmed \| returned \| cancelled` |
| `expires_at` | timestamp, nullable — held rows expire so abandoned carts free the calendar |
| `created_at`, `updated_at` | |

Indexes:

| Name / columns | Purpose |
|---|---|
| `rental_res_availability_idx` (`product_id`, `status`, `starts_on`, `ends_on`) | Overlap queries |
| `cart_id` | Cart holds |
| `order_id` | Order show / return |
| (`status`, `expires_at`) | `rentals:release-expired-holds` |

Confirm at place does **not** null `cart_id`. Scheduler does **not** delete rows; it sets `status = cancelled`.

---

## Rental columns on other tables

Full table docs: [catalog-products/database.md](../catalog-products/database.md), [cart-checkout/database.md](../cart-checkout/database.md), [orders/database.md](../orders/database.md).

### `products`

| Migration | Column | Notes |
|---|---|---|
| `2026_07_03_100000_add_sale_mode_to_products_table.php` | `sale_mode` | string(10), default `sale`, indexed. Comment: `sale \| rental \| both` |
| `2026_07_03_110000_add_rental_end_inclusive_to_products_table.php` | `rental_end_inclusive` | boolean, default `true`. `true` = end day billed/occupied; `false` = end day is return day |

### `carts`, `cart_items`, `orders`, `order_items`

`database/migrations/2026_07_03_100300_add_rental_fields_to_carts_and_orders.php`

**`cart_items` and `order_items` (same rental columns):**

| Column | Notes |
|---|---|
| `line_type` | string(10), default `sale` — comment `sale \| rental` |
| `rental_starts_on`, `rental_ends_on` | date, nullable |
| `rental_periods` | unsigned int, nullable |
| `rental_period_unit` | string(10), nullable |
| `deposit_total` | decimal(12,2), default `0` |

**`carts` and `orders`:** `deposit_total` decimal(12,2), default `0`, after `shipping_total`.

No other rental migrations in this repository.
