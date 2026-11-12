# Routes — Warehouses & Inventory

Back to [module README](README.md). Controller: [controllers.md](controllers.md).

## Web (admin)

Defined in `routes/web.php` inside:

```text
middleware: auth, verified.if-required, set.user.locale, role:Admin
prefix: admin
name: admin.
```

Plus controller `check.permission:warehouse_*` as below.

| Method | URI | Name | Controller | Group middleware | Permission |
| ------ | --- | ---- | ---------- | ---------------- | ---------- |
| GET, POST | `/admin/warehouses` | `admin.warehouses.index` | `WarehouseController@index` | auth, verified.if-required, set.user.locale, role:Admin | `warehouse_view` |
| GET, POST | `/admin/warehouses/create` | `admin.warehouses.create` | `WarehouseController@create` | same | `warehouse_create` |
| GET, POST | `/admin/warehouses/{id}/edit` | `admin.warehouses.edit` | `WarehouseController@edit` | same | `warehouse_edit` |
| DELETE | `/admin/warehouses/{id}` | `admin.warehouses.delete` | `WarehouseController@delete` | same | `warehouse_delete` |

`{id}` is an integer warehouse id (`findOrFail`).

Create and edit use **POST on the same URI as GET** (not `PUT`/`PATCH`). Delete uses method spoofing (`@method('DELETE')`) from `admin.warehouses.actions`.

Sidebar: `resources/views/layouts/components/main-sidebar.blade.php` — `@can('warehouse_view')` links to `admin.warehouses.index`.

## API

No warehouse or inventory routes in `routes/api.php`.

Storefront stock flags come from product projections (`in_stock`, payload `inventory.in_stock`), owned by catalog-products.

## Internal / webhook

None.

## Permissions

Seeded in `database/seeders/ModulePermissionSeeder.php` under module display name `Warehouses`:

| Permission | Protects |
| ---------- | -------- |
| `warehouse_view` | Index / DataTables; sidebar |
| `warehouse_create` | Create GET+POST; index “Add” button `@can('warehouse_create')` |
| `warehouse_edit` | Edit GET+POST; row action `@can('warehouse_edit')` |
| `warehouse_delete` | DELETE; row action `@can('warehouse_delete')` |

Blade actions wrapper: `@canany(['warehouse_edit', 'warehouse_delete'])`.

Product inventory fields on the product form are gated by **product** permissions, not `warehouse_*`.
