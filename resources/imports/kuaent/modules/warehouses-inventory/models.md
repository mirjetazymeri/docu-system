# Models — Warehouses & Inventory

Back to [module README](README.md).

Owned models: `Warehouse`, `ProductInventory`.

Related inventory methods on `App\Models\Product` belong to [catalog-products](../catalog-products/README.md). They are summarized here because they are the public API for stock totals.

No observers are registered for these models (no `WarehouseObserver` / `ProductInventoryObserver` found).

---

## Warehouse

`App\Models\Warehouse`

Source: `app/Models/Warehouse.php`

### Purpose

A physical or logical stock location. Inventory rows point at a warehouse. Admin product forms only offer **active** warehouses.

### Table

```text
warehouses
```

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `id` | bigint PK | Identifier |
| `code` | string, unique | Stable warehouse code (seeded `default`) |
| `name` | string | Display name |
| `is_active` | boolean, default true | Included in `scopeActive` / product form |
| `is_default` | boolean, default false | Used by `Warehouse::default()` and new-product inventory fallback |
| `country` | string, nullable | Free-text location (not a countries/locales FK) |
| `city` | string, nullable | Free-text location |
| `meta` | json, nullable | Fillable; not set by `WarehouseController` or the admin form |
| `created_at` / `updated_at` | timestamps | Laravel timestamps |

Never store secrets in `meta` via this controller; the field is unused in warehouse admin.

### Casts

| Attribute | Cast |
| --------- | ---- |
| `is_active` | `boolean` |
| `is_default` | `boolean` |
| `meta` | `array` |

### Fillable

`code`, `name`, `is_active`, `is_default`, `country`, `city`, `meta`

### Relationships

The model **does not** declare `inventories()`. The inverse exists on `ProductInventory::warehouse()`.

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| — | — | `ProductInventory` | Implied by `product_inventory.warehouse_id` FK; not defined on this model |

### Scopes

| Scope | Behavior |
| ----- | -------- |
| `scopeActive($q)` | `where('is_active', true)` |

Used by `ProductController::create()` / `edit()`: `Warehouse::active()->get()`.

### Accessors / Mutators

None.

### Business Methods

| Method | Behavior |
| ------ | -------- |
| `static default(): ?self` | `where('is_default', true)->first()` — first matching row; schema does not unique-constrain `is_default` |

### Events / Observers

None found.

---

## ProductInventory

`App\Models\ProductInventory`

Source: `app/Models/ProductInventory.php`

### Purpose

On-hand and reserved quantity for one product in one warehouse.

### Table

```text
product_inventory
```

The model sets `protected $table = 'product_inventory'`. This is **not** the table `product_inventories` created in `database/migrations/2025_08_25_153836_create_products_table.php` (legacy per-product inventory; unused by this model).

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `id` | bigint PK | Identifier; checkout deducts FIFO by `orderBy('id')` |
| `product_id` | FK → `products`, cascade delete | Catalog product |
| `warehouse_id` | FK → `warehouses`, cascade delete | Warehouse |
| `qty` | integer, default 0 | On-hand units (checkout deducts this column) |
| `reserved_qty` | integer, default 0 | Subtracted from availability; not auto-incremented by checkout |
| `created_at` / `updated_at` | timestamps | Laravel timestamps |

Unique: `(product_id, warehouse_id)`.

### Casts

| Attribute | Cast |
| --------- | ---- |
| `qty` | `integer` |
| `reserved_qty` | `integer` |

### Fillable

`product_id`, `warehouse_id`, `qty`, `reserved_qty`

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `product()` | `belongsTo` | `App\Models\Product` | Catalog product |
| `warehouse()` | `belongsTo` | `App\Models\Warehouse` | Warehouse |

### Scopes

None.

### Accessors / Mutators

None.

### Business Methods

| Method | Behavior |
| ------ | -------- |
| `available(): int` | `return max(0, $this->qty - $this->reserved_qty);` |

### Events / Observers

None found.

---

## Related: `App\Models\Product` inventory surface

Owned by catalog-products (`app/Models/Product.php`). Documented here only for stock semantics.

| Method | Behavior |
| ------ | -------- |
| `inventories()` | `hasMany(ProductInventory::class)` |
| `totalQty()` | Sum of `qty` on loaded `inventories` |
| `availableQty()` | Bundle → `BundleAvailabilityService::availableQty($this)`. Configurable → `1` if any active child has `sum(qty - reserved_qty) > 0`, else `0`. Else sum of `qty - reserved_qty` on this product’s inventories |
| `isInStock()` | `availableQty() > 0` |
| `DEFAULT_NEW_STOCK_QTY` | `999` — used when creating inventory without an explicit qty |

Constant comment: “Default on-hand qty applied only when creating inventory that omitted qty.”
