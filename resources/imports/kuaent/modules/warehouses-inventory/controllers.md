# Controllers — Warehouses & Inventory

Back to [module README](README.md). Routes: [routes.md](routes.md).

This module owns one controller. Product inventory on the catalog form is handled by `ProductController` in [catalog-products](../catalog-products/README.md). Order cancel restock is `OrderController` in [orders](../orders/README.md).

---

## `App\Http\Controllers\Admin\WarehouseController`

Source: `app/Http/Controllers/Admin/WarehouseController.php`

### Responsibility

Admin warehouse list (Yajra DataTables), create, update, delete. No inventory qty editing here.

### Dependencies

Constructor middleware only (no constructor injection):

| Middleware | Actions |
| ---------- | ------- |
| `check.permission:warehouse_view` | `index` |
| `check.permission:warehouse_create` | `create` |
| `check.permission:warehouse_edit` | `edit` |
| `check.permission:warehouse_delete` | `delete` |

Alias `check.permission` → `App\Http\Middleware\AutoAuthorizePermission` (`bootstrap/app.php`): allows Super Admin, else `hasAccessLevelPermission($permission)`, else HTTP 403.

Uses: `Warehouse`, `Request`, `DB`, `Rule`, `DataTables`.

Route group (also applies): `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`, prefix `admin`, name `admin.`.

### Methods

#### `index(Request $request)`

- **Route:** `GET|POST admin/warehouses` — `admin.warehouses.index`
- **Auth / permission:** logged-in Admin + `warehouse_view`
- **Validation:** none
- **Behavior:** If `$request->ajax()`, Eloquent DataTables on `Warehouse::query()->orderByDesc('id')`. Columns:
  - `location`: `trim(country / city)` or `—`; `filterColumn` searches `country` or `city` LIKE
  - `is_active` / `is_default`: translated yes/no
  - `actions`: `admin.warehouses.actions` (edit/delete gated by `@can`)
- **Writes / events / jobs:** none
- **Response:** JSON (ajax) or view `admin.warehouses.index`

DataTables JS uses GET ajax to the same named route.

#### `create(Request $request)`

- **Route:** `GET|POST admin/warehouses/create` — `admin.warehouses.create`
- **Permission:** `warehouse_create` (GET form and POST save)
- **Validation:** `validateData()` on POST
- **On POST:** `DB::transaction`: if `is_default` is non-empty, `Warehouse::query()->update(['is_default' => false])`, then `Warehouse::create($data)`
- **Events / jobs:** none
- **Response:** redirect `admin.warehouses.index` with flash `Warehouse created.` or view `admin.warehouses.create`

Create/edit forms POST to the current URL (`method="POST"`, no `action`).

#### `edit(Request $request, int $id)`

- **Route:** `GET|POST admin/warehouses/{id}/edit` — `admin.warehouses.edit`
- **Permission:** `warehouse_edit`
- **Load:** `Warehouse::findOrFail($id)`
- **On POST:** `validateData($request, $warehouse->id)` then transaction: if `is_default` non-empty, set other warehouses’ `is_default` to false, then `$warehouse->update($data)`
- **Response:** redirect with `Warehouse updated.` or view `admin.warehouses.edit`

Unchecking `is_default` does not pass the field (`sometimes|boolean` + checkbox without hidden `0`), so the update payload may omit `is_default` / `is_active` and those columns stay unchanged.

#### `delete(int $id)`

- **Route:** `DELETE admin/warehouses/{id}` — `admin.warehouses.delete`
- **Permission:** `warehouse_delete`
- **Load:** `findOrFail`
- **Rule:** if `$warehouse->is_default`, `back()` with error `Default warehouse cannot be deleted.`
- **Otherwise:** `$warehouse->delete()` — FK cascade deletes `product_inventory` rows for that warehouse
- **Response:** redirect with `Warehouse deleted.`

No check for remaining inventory before delete (cascade handles rows).

#### `validateData(Request $request, ?int $id = null): array`

Inline rules (no FormRequest):

| Field | Rules |
| ----- | ----- |
| `code` | required, string, max 100, unique `warehouses.code` ignoring `$id` |
| `name` | required, string, max 255 |
| `country` | nullable, string, max 100 |
| `city` | nullable, string, max 100 |
| `is_active` | sometimes, boolean |
| `is_default` | sometimes, boolean |

`meta` is fillable on the model but not validated or accepted here.

---

## Related controllers (not owned)

### `App\Http\Controllers\Admin\ProductController`

Source: `app/Http/Controllers/Admin/ProductController.php`

- `create` / `edit`: `$warehouses = Warehouse::active()->get()` passed to product views.
- `edit`: eager-loads `inventories` (and child inventories for bundles/configurables). Variant prefill uses **`$child->inventories->first()`** only for `qty` / `warehouse_id`.
- `index` DataTables `stock` column: bundles use `availableQty()`; configurables use `isInStock()`; simples use `withSum` total/reserved.

Inventory POST body is validated by `StoreProductRequest` / `UpdateProductRequest` and persisted by `ProductService`, not `WarehouseController`.

### `App\Http\Controllers\Admin\OrderController`

`performCancel()` restocks `ProductInventory` for the order line `product_id`. See [services.md](services.md).
