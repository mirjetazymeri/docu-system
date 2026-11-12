# Models

Back to [README](README.md).

This module owns four Eloquent models. None define `$casts`, `$hidden`, `$appends`, `$table`, local/global scopes, accessors/mutators, observers, or `$dispatchesEvents`. Table names are Laravel defaults.

`App\Models\User`, `App\Models\Product`, and `App\Models\Channel` do **not** declare inverse wishlist/compare relations.

---

## `App\Models\Wishlist`

Source: `app/Models/Wishlist.php`

### Purpose

Header row for a storefront wishlist (guest or user), scoped by channel.

### Table

```text
wishlists
```

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `id` | bigint PK | List id |
| `channel_id` | FK, nullable | Sales channel; `nullOnDelete` |
| `user_id` | FK, nullable | Owner when attached to a user; `nullOnDelete` |
| `session_token` | uuid, nullable, unique | Guest token; user-created lists pass `null` |
| `status` | string, default `active` | Queried as `active` in the API controllers; this module only writes `active` |
| `items_count` | unsigned int, default `0` | Denormalized count of `wishlist_items` |
| `created_at` / `updated_at` | timestamps | Laravel timestamps |

Fillable matches those columns (except `id` / timestamps).

### Casts

None declared on the model.

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `items()` | `hasMany` | `App\Models\WishlistItem` | Lines on this wishlist |
| `user()` | `belongsTo` | `App\Models\User` | Optional owner |

No `channel()` relation is declared, despite `channel_id` being fillable and present on the table.

### Scopes / Accessors / Business Methods / Events

None on the model. List lookup, guest merge, and `items_count` updates live in `WishlistController`.

---

## `App\Models\WishlistItem`

Source: `app/Models/WishlistItem.php`

### Purpose

One product on a wishlist. Quantity is not stored.

### Table

```text
wishlist_items
```

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `id` | bigint PK | Item id used by `DELETE …/wishlist/items/{id}` |
| `wishlist_id` | FK, cascade delete | Parent wishlist |
| `product_id` | FK, cascade delete | Catalog product |
| `created_at` / `updated_at` | timestamps | Laravel timestamps |

Unique: `(wishlist_id, product_id)`.

Fillable: `wishlist_id`, `product_id`.

### Casts

None declared.

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `wishlist()` | `belongsTo` | `App\Models\Wishlist` | Parent list |
| `product()` | `belongsTo` | `App\Models\Product` | Catalog product |

### Scopes / Accessors / Business Methods / Events

None.

---

## `App\Models\Compare`

Source: `app/Models/Compare.php`

### Purpose

Header row for a storefront compare list (guest or user), scoped by channel. Shape matches `Wishlist`.

### Table

```text
compares
```

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `id` | bigint PK | List id |
| `channel_id` | FK, nullable | Sales channel; `nullOnDelete` |
| `user_id` | FK, nullable | Owner when attached to a user; `nullOnDelete` |
| `session_token` | uuid, nullable, unique | Guest token; user-created lists pass `null` |
| `status` | string, default `active` | Queried as `active` in the API controllers; this module only writes `active` |
| `items_count` | unsigned int, default `0` | Denormalized count of `compare_items` |
| `created_at` / `updated_at` | timestamps | Laravel timestamps |

Fillable matches those columns (except `id` / timestamps).

### Casts

None declared.

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `items()` | `hasMany` | `App\Models\CompareItem` | Lines on this compare list |
| `user()` | `belongsTo` | `App\Models\User` | Optional owner |

No `channel()` relation is declared.

### Scopes / Accessors / Business Methods / Events

None on the model. Equivalent logic lives in `CompareController`.

---

## `App\Models\CompareItem`

Source: `app/Models/CompareItem.php`

### Purpose

One product on a compare list. Quantity is not stored.

### Table

```text
compare_items
```

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `id` | bigint PK | Item id used by `DELETE …/compare/items/{id}` |
| `compare_id` | FK, cascade delete | Parent compare list |
| `product_id` | FK, cascade delete | Catalog product |
| `created_at` / `updated_at` | timestamps | Laravel timestamps |

Unique: `(compare_id, product_id)`.

Fillable: `compare_id`, `product_id`.

### Casts

None declared.

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `compare()` | `belongsTo` | `App\Models\Compare` | Parent list |
| `product()` | `belongsTo` | `App\Models\Product` | Catalog product |

### Scopes / Accessors / Business Methods / Events

None.

---

## Product fields used by API resources (owned by catalog-products)

Resources load `items.product.translations`, `items.product.images`, and `items.product.prices`. They call `Product::priceForChannel()` and `Product::effectivePrice()` for unit/final prices. Translation name/slug (and compare short/description) come from `$product->translations->first()`, with fallbacks to `$product->name` / `$product->slug` (those attributes are not on `Product::$fillable`; after `2026_03_26_164158_drop_price_from_products_table.php`, `products.price` is also dropped — `WishlistResource` / `CompareResource` still call `$product->getAttribute('price')` as a fallback).

Image resolution walks several attribute names; `ProductImage` fillable includes `path`, `alt`, `is_primary` (see [catalog-products](../catalog-products/README.md)).

`CompareResource` also reads `data_get($product, 'inventory.is_in_stock', true)` and `data_get($product, 'rating.avg'|'rating.count', …)`. `Product` defines `inventories()` and `reviews()`, not `inventory` or `rating`. Controllers do not eager-load those relations. The JSON keys therefore use the `data_get` defaults unless a same-named attribute exists at runtime.
