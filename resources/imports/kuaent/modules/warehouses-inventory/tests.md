# Tests — Warehouses & Inventory

Back to [module README](README.md).

Repository search: `tests/` for `Warehouse`, `ProductInventory`, `product_inventory`, warehouse routes, `WarehouseController`.

## Warehouse admin

| Test | Type | Covers |
| ---- | ---- | ------ |
| — | — | **No test class** references `WarehouseController`, `admin.warehouses`, or warehouse CRUD |

There is no `WarehouseFactory`. Feature tests that need a warehouse **create one in setUp**.

---

## Tests that touch inventory (owned by catalog / checkout suites)

These assert stock behavior; they are not warehouse-admin tests.

### Unit

| Test | File | Covers |
| ---- | ---- | ------ |
| `test_dining_set_example_uses_min_of_floored_ratios` | `tests/Unit/BundleAvailabilityServiceTest.php` | Bundle math |
| `test_zero_when_any_child_is_out_of_stock` | same | Child available 0 → bundle 0 |
| `test_zero_when_a_child_has_insufficient_stock_for_one_bundle` | same | `floor(5/6) = 0` |
| `test_empty_components_returns_zero` | same | |
| `test_exact_division_across_multiple_children` | same | |
| `test_single_child_floor_division` | same | |
| `test_available_qty_for_hydrated_bundle_product` | same | Eloquent adapter |
| `test_available_qty_accounts_for_reserved_stock` | same | `qty - reserved_qty` |
| `test_available_qty_sums_multiple_warehouses_per_child` | same | Two `ProductInventory` rows summed |
| `test_non_bundle_product_returns_zero_from_service` | same | Service returns 0 for simple |

`BundleAvailabilityServiceTest` builds models in memory (`setRelation`); it does not hit the database.

### Feature (MySQL-only)

`BundleTestCase` / `ConfigurableTestCase` skip unless `DB_CONNECTION=mysql`. They `Warehouse::query()->create([...])` then pass `warehouse_id` into `ProductService::create` inventory.

| Test | File | Covers |
| ---- | ---- | ------ |
| `test_creating_a_simple_product_still_works` | `tests/Feature/Bundle/BundleProductTest.php` | `product_inventory` row qty; `availableQty()` |
| `test_creating_a_bundle_product_persists_items_and_keeps_no_stock` | same | Bundle parent has **zero** inventory rows |
| `test_bundle_availability_is_min_of_floored_child_ratios` | same | `availableQty` / `isInStock` on bundle |
| `test_cart_accepts_a_bundle_within_availability` | `tests/Feature/Bundle/BundleCartCheckoutTest.php` | Cart add within calculated stock |
| `test_cart_rejects_a_bundle_above_calculated_availability` | same | Cart `RuntimeException` when over availability |
| `test_checkout_deducts_stock_from_child_products` | same | Place order deducts children; bundle parent row count stays 0 |
| `test_simple_product_checkout_still_deducts_its_own_stock` | same | Simple `qty` reduced |
| `test_creating_configurable_builds_unique_children_and_wipes_parent_inventory` | `tests/Feature/Configurable/ConfigurableProductTest.php` | Parent `ProductInventory` count 0 |
| `test_parent_in_stock_when_any_child_has_stock_does_not_sum` | same | Parent `availableQty()` is `1`, not sum of children |

`BundleCartCheckoutTest::stockOf()` sums `qty` only (`ProductInventory::where('product_id')->sum('qty')`), not `available()`.

---

## Apparently not covered (searched, not found)

Do not treat as a complete audit of all test names; these scenarios have **no matching test files/names** in `tests/`:

- Warehouse create / unique `code` / default-flag clearing / cannot delete default
- Cascade delete of `product_inventory` when a warehouse is deleted
- `OrderService` insufficient-stock / stock-conflict exceptions for simple products
- Checkout deduct FIFO across **multiple** warehouse rows
- `reserved_qty` excluding units from checkout deduct
- Order cancel restock / credit-note restock (warehouse selection)
- `MailService::notifyAdminLowStock` / `LowStockMail` production path
- Admin `filter_stock=low_stock` SQL
- `Warehouse::default()` / `ProductService` empty-inventory fallback to default warehouse
- Inactive warehouses omitted from product form

Low-stock mail can be sent as a **template sample** via `php artisan` `TestMailCommand` type `admin-low-stock`; that is not an automated test.
