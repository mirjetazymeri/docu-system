# Tax database

Migrations owned or required by this module. Filenames below are the files on disk (some catalog migrations use a `.php.php` suffix).

## Tables this module creates

### `tax_classes`

**Migration:** `database/migrations/2025_08_29_204018_create_tax_classes_table.php.php`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigint PK | |
| `code` | string(64) | unique |
| `name` | string(191) | |
| `created_at` / `updated_at` | timestamps | |

Migration comment on `code`: `'standard','reduced','zero'` — documentation of intended values only; not enforced.

### `tax_rates`

**Migration:** `database/migrations/2025_08_29_204154_create_tax_rates_table.php.php`

| Column | Type | Constraints / defaults |
|--------|------|------------------------|
| `id` | bigint PK | |
| `code` | string(64) | unique |
| `name` | string(191) | |
| `country_iso2` | char(2) nullable | null = any country |
| `state_iso2` | string(8) nullable | null = any state |
| `postcode` | string(32) nullable | exact match or null |
| `is_zip_range` | boolean | default `false` |
| `zip_from` | string(32) nullable | |
| `zip_to` | string(32) nullable | |
| `rate_percent` | decimal(7, 4) | e.g. `20.0000` |
| `priority` | unsigned integer | default `0`; lower first |
| `compound` | boolean | default `false` |
| `is_active` | boolean | default `true` |
| `created_at` / `updated_at` | timestamps | |

Indexes:

- `(country_iso2, state_iso2)`
- `(postcode)`
- `(is_zip_range, zip_from, zip_to)`
- `(priority, is_active)`

No foreign key from `country_iso2` to `countries.code`.

### `tax_rules` (class–rate pivot)

**Migration:** `database/migrations/2025_08_29_204241_create_tax_rules_table.php.php`

| Column | Type | Constraints |
|--------|------|-------------|
| `id` | bigint PK | |
| `tax_class_id` | FK → `tax_classes` | `cascadeOnDelete` |
| `tax_rate_id` | FK → `tax_rates` | `cascadeOnDelete` |
| `sort_order` | unsigned integer | default `0` |
| `created_at` / `updated_at` | timestamps | |

Unique: `(tax_class_id, tax_rate_id)`.

## Columns this module adds on other tables

### `products.tax_class_id`

**Migration:** `database/migrations/2025_08_29_204303_add_tax_class_to_products_table.php.php`

| Column | Type | Constraints |
|--------|------|-------------|
| `tax_class_id` | nullable FK → `tax_classes` | `nullOnDelete`; added after `type` |

A product with `tax_class_id` null yields **no applicable rates** (`TaxService::applicableRates` returns an empty collection).

### `orders.prices_include_tax` and `orders.tax_breakdown`

**Migration:** `database/migrations/2026_07_16_160000_add_tax_snapshot_to_orders_table.php`

Added only if missing:

| Column | Type | Placement |
|--------|------|-----------|
| `prices_include_tax` | boolean nullable | after `tax_total` |
| `tax_breakdown` | JSON nullable | after `prices_include_tax` |

`Order` casts: `prices_include_tax` boolean, `tax_breakdown` array.

`tax_breakdown` rows written at place-time match `TaxService::calculate()` `breakdown`: `code`, `label`, `rate`, `amount`.

## Tax-related columns created by checkout/orders (not this module’s migrations)

Documented because snapshots land here.

### `carts`

**Migration:** `database/migrations/2025_08_29_183759_create_carts_table.php.php`

| Column | Role for tax |
|--------|----------------|
| `tax_total` | decimal(12,2), default 0 — filled from `TaxService` `total_tax` on recalc |
| `subtotal`, `discount_total`, `shipping_total`, `grand_total` | also overwritten from the same quote |
| `billing` / `shipping` JSON | destination (`country_code` / `country`, `state_code` / `state`, `postcode`); shipping `amount` is the shipping gross |
| `meta` JSON | `meta.tax_quote` stores the full quote (see [services.md](services.md)) |

Coupon columns used by tax discount eligibility: `coupon_id`, `coupon_code`, `coupon_discount` (`database/migrations/2025_08_29_195759_add_coupon_to_carts_table.php.php`).

### `orders` / `order_items`

**Migration:** `database/migrations/2025_09_01_145651_create_orders_domain.php.php`

| Table | Column | Role |
|-------|--------|------|
| `orders` | `tax_total` decimal(12,2) | place-time `total_tax` |
| `order_items` | `tax` decimal(12,2) | per-line tax from `line_taxes[cart_item_id]` |
| `order_items` | `meta` JSON | `net_total`, `tax_total` copied from `line_nets` / `line_taxes` |

Credit notes (`database/migrations/2026_07_16_170000_create_credit_notes_tables.php`) also have `prices_include_tax` and `tax_breakdown`. Those are filled by `CreditNoteService` from the **order snapshot and refunded line taxes**, not by `TaxService`.

## Entity relationship (tax catalog)

```
tax_classes 1 ──< tax_rules >── 1 tax_rates
     │
     └── 0..* products.tax_class_id
```

Checkout does not persist which `tax_rate` IDs were used. The order stores aggregated `tax_breakdown` (code/label/rate/amount) and line `tax` amounts.
