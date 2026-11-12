# Warehouses & Inventory

## Purpose

This module owns **warehouse master data** and the **per-product, per-warehouse stock table** (`product_inventory`). Sellable availability is derived from on-hand quantity minus reserved quantity. Physical stock is stored only on simple (and configurable-child) products; bundles and configurable parents do not keep their own inventory rows.

There is no dedicated inventory service, repository, job, observer, or API in this module. Warehouse CRUD lives here. Stock **writes** and **stock checks** are performed by [catalog-products](../catalog-products/README.md), [cart-checkout](../cart-checkout/README.md), and [orders](../orders/README.md). Those operations are summarized in [services.md](services.md) and [Important Business Rules](#important-business-rules) without duplicating those modules’ full documentation.

## Responsibilities

- Admin CRUD for warehouses (`code`, `name`, location, active, default).
- Enforce a single default warehouse at write time (application-level, not a unique DB index).
- Block deletion of the default warehouse.
- Own the `warehouses` and `product_inventory` tables and Eloquent models.
- Seed a default warehouse.

This module does **not**:

- Expose a storefront or REST inventory API.
- Own product create/edit UI (inventory fields are on the product form in catalog-products).
- Own checkout, order cancel, or credit-note restock.
- Trigger low-stock email in production code (see [Low-stock mail](#low-stock-mail)).

## Source Code Locations

```text
app/Models/Warehouse.php
app/Models/ProductInventory.php
app/Http/Controllers/Admin/WarehouseController.php
resources/views/admin/warehouses/
database/migrations/2026_03_18_154902_create_warehouses_table.php
database/migrations/2026_03_18_161339_create_product_inventory_table_v2.php
database/seeders/WarehouseSeeder.php
routes/web.php                          (admin warehouses routes)
```

Related (owned by other modules; inventory operations only):

```text
app/Services/Product/ProductService.php              (syncInventory)
app/Services/Product/ConfigurableProductService.php  (syncChildInventory)
app/Services/Product/BundleAvailabilityService.php   (reads child inventories)
app/Services/Product/ProductAdminFilterService.php   (stock filters)
app/Services/Checkout/OrderService.php               (check + deduct)
app/Services/Checkout/CartService.php                (bundle availability on add/update)
app/Services/Checkout/CreditNoteService.php          (optional restock)
app/Http/Controllers/Admin/OrderController.php       (cancel restock)
app/Http/Controllers/Admin/ProductController.php     (loads active warehouses)
app/Mail/Admin/LowStockMail.php
app/Services/Mail/MailService.php                    (notifyAdminLowStock)
```

## Main Components

| Type | Class | Responsibility |
| ---- | ----- | -------------- |
| Model | `App\Models\Warehouse` | Warehouse master; `scopeActive`, `default()` |
| Model | `App\Models\ProductInventory` | Per product + warehouse `qty` / `reserved_qty`; `available()` |
| Controller | `App\Http\Controllers\Admin\WarehouseController` | Admin list/create/edit/delete |
| Seeder | `Database\Seeders\WarehouseSeeder` | Upserts code `default` / “Main Warehouse” |
| Mail | `App\Mail\Admin\LowStockMail` | Queued admin low-stock template (no production dispatch found) |

No FormRequest, Policy, Job, Event, Listener, Command, Repository, or Action class is owned by this module. Warehouse validation is inline on `WarehouseController`.

## Module Dependencies

Verified from imports, FKs, and route groups. See [dependencies.md](dependencies.md).

```text
This module depends on:
- catalog-products   (product_inventory.product_id → products; Product::inventories())
- users-access-control (admin auth, role:Admin, warehouse_* permissions)

This module does not depend on:
- channels-locales   (no Channel/Locale FK or import on Warehouse / ProductInventory / WarehouseController)

This module is used by:
- catalog-products   (product form, ProductService inventory sync, projections)
- cart-checkout      (OrderService stock check/deduct; CartService bundle checks)
- orders             (cancel restock; CreditNoteService restock)
```

Coordinator-suggested dependency `channels-locales` was **not** confirmed in this module’s code. Warehouse `country` / `city` are unconstrained strings, not locale or channel FKs. Stock is not partitioned by channel.

## Entry Points

| Kind | Entry |
| ---- | ----- |
| Web | `admin/warehouses*` under `auth`, `verified.if-required`, `set.user.locale`, `role:Admin` — see [routes.md](routes.md) |
| API | None in `routes/api.php` |
| Commands | None owned. `TestMailCommand` type `admin-low-stock` can send a **sample** `LowStockMail` |
| Scheduled tasks | None found that scan inventory or send low-stock mail |
| Queue jobs | None owned. `LowStockMail` implements `ShouldQueueAfterCommit` if it were queued |
| Events | None |
| Seed | `WarehouseSeeder` via `DatabaseSeeder` (after Channel/Country/City seeders) |

Product admin inventory UI and checkout stock mutations enter through other modules.

## Important Business Rules

Extracted from code. Details and call sites: [services.md](services.md).

- **Stock location.** Sellable stock lives in `product_inventory` keyed uniquely by `(product_id, warehouse_id)`. The `ProductInventory` model maps to table `product_inventory` (not the older `product_inventories` table created in the 2025 products migration).
- **Available qty (row).** `ProductInventory::available()` = `max(0, qty - reserved_qty)`.
- **Available qty (simple product).** `Product::availableQty()` sums `qty - reserved_qty` across all warehouse rows (no `max(0)` per row). `isInStock()` is `availableQty() > 0`.
- **Bundles hold no stock.** `ProductService::syncBundleComposition()` deletes all `product_inventory` rows for a bundle parent. Availability is `min(floor(child_available / required_qty))` via `BundleAvailabilityService`.
- **Configurable parents hold no stock.** Parent inventory rows are deleted on variant sync. Parent `availableQty()` returns `1` if any **active** child has `sum(qty - reserved_qty) > 0`, otherwise `0` (not a sum of child quantities). Children are simple products with their own warehouse rows.
- **Default new qty.** `Product::DEFAULT_NEW_STOCK_QTY` is `999`, applied when creating inventory that omitted `qty` (`ProductService::syncInventory`, `ConfigurableProductService::syncChildInventory`, and `StoreProductRequest` for new variants).
- **Default warehouse on new simple product.** If create payload has an empty `inventory` array, `ProductService` inserts a row for the default warehouse (`is_default = true`), else the first warehouse id.
- **Admin product form only lists active warehouses** (`Warehouse::active()`).
- **Warehouse code** is unique. **At most one default** is maintained by clearing `is_default` on other rows inside a DB transaction when creating/editing with `is_default` set. The schema has no unique index on `is_default`.
- **Default warehouse cannot be deleted.** Other deletes cascade to `product_inventory` (`cascadeOnDelete` on `warehouse_id`).
- **Checkout stock check (sale lines).** Inside `OrderService::place()` transaction: skip rentals; for bundles, each child must have `availableProductQty(child) >= line_qty * bundle_item.quantity`; otherwise the line product must have `availableProductQty >= line_qty`. Availability is `sum(max(0, qty - reserved_qty))` with `lockForUpdate`.
- **Checkout deduct.** `deductProductStock()` subtracts from `qty` (not `reserved_qty`), FIFO by inventory row `id`, still using available = `max(0, qty - reserved_qty)`. If remaining qty cannot be filled: `RuntimeException("Stock conflict detected while placing order for {$label}.")`. Bundles deduct children, not the parent. Rentals do not deduct sellable stock.
- **Cart add/update does not check simple-product stock.** `CartService` only calls `assertBundleAvailability()` (no-op for non-bundles). Comment in `addItem()`: simple products are “validated at checkout”.
- **`reserved_qty` is not incremented by checkout.** No application code does `reserved_qty += …`. It is written from admin product payload (and demo seeder). Orders reduce `qty`.
- **Order cancel restock.** `OrderController::performCancel()` adds `restockableQtyOnCancel` onto the **first** `product_inventory` row for the **order line `product_id`** (no warehouse order). Bundle lines restock that bundle `product_id`, not the children that were deducted. Rentals are skipped.
- **Credit-note restock.** When `restock` is true, `CreditNoteService` uses `ProductInventory::firstOrNew(['product_id' => …])` (no `warehouse_id`) and adds qty.
- **Rental total units.** `RentalAvailabilityService::totalUnits()` sums **`qty` only** (ignores `reserved_qty`) across warehouses; occupancy is `rental_reservations`, not `reserved_qty`.
- **Projections.** After order commit, `OrderService` rebuilds product projections for deducted SKUs and their bundle/configurable parents so storefront `in_stock` / `qty` refresh.
- **Low-stock mail is not triggered by this module.** `MailService::notifyAdminLowStock()` is never called from other application classes. Admin product list has a separate `low_stock` **filter**.

## Low-stock mail

| Piece | Owner | Role |
| ----- | ----- | ---- |
| `App\Mail\Admin\LowStockMail` | Mail / notifications | Mailable; payload `Collection` of `{sku, name, qty}`; queued after commit |
| `MailService::notifyAdminLowStock()` | Mail | Sends only if `setting('notifications.admin.notify_low_stock')` and `$items` is non-empty |
| Production caller | — | **None found** (repository-wide search) |
| Manual sample | `TestMailCommand` (`admin-low-stock`) | Sends fake items; not an inventory scan |
| Admin UI filter | catalog-products | `filter_stock=low_stock` vs `filter_low_stock_threshold` (default 5) using `SUM(qty - reserved_qty)` on **that product’s** inventory rows |
| Settings keys | `config/settings.php` | `notifications.admin.notify_low_stock` (default true), `notifications.admin.low_stock_threshold` (default 5) |

**This module does not own the trigger.** There is no observer, job, or scheduler in this folder that compares stock to the threshold and queues `LowStockMail`.

## Views

```text
resources/views/admin/warehouses/index.blade.php
resources/views/admin/warehouses/create.blade.php
resources/views/admin/warehouses/edit.blade.php
resources/views/admin/warehouses/_form.blade.php
resources/views/admin/warehouses/actions.blade.php
resources/views/layouts/components/main-sidebar.blade.php   (@can warehouse_view)
resources/views/admin/products/_inventory.blade.php         (catalog-products UI)
resources/views/emails/admin/low-stock.blade.php
```

Form checkboxes `is_active` / `is_default` have no hidden `0` field. Controller validation uses `sometimes|boolean`, so an unchecked box is omitted from `$data` and is not persisted as `false` on update.

## Documentation Index

- [models.md](models.md)
- [database.md](database.md)
- [controllers.md](controllers.md)
- [routes.md](routes.md)
- [services.md](services.md) — inventory operations in other modules
- [dependencies.md](dependencies.md)
- [tests.md](tests.md)
