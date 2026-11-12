# Orders — tests

Coverage for this module is **sparse**. There is no Feature test for `Admin\OrderController`, `OrderShipmentAssignmentController`, `MyOrderController`, `OrderObserver`, `InvoiceService`, or assignment sync.

## Tests that touch order code

### `tests/Unit/Pricing/DiscountEligibilityAndRefundTest.php`

PHPUnit `TestCase` (not `Tests\TestCase` — **no** Laravel app/DB).

- Discount allocation on `OrderTotalsCalculator` (pricing domain; not order HTTP).
- `CreditNoteService::quote()` on **in-memory** `Order` / `OrderItem` with `setRelation` (no `issue()`, no inventory, no payments):
  - `test_partial_line_refund_is_proportional` — qty 1 of 3 → line gross 120, tax 20, net 100.
  - `test_refund_tax_does_not_exceed_original_line_tax_on_full_qty` — full qty tax 20.

Does not hit admin `refund` validation or status transition to `refunded`.

### `tests/Feature/Bundle/BundleCartCheckoutTest.php`

**Checkout-owned.** Uses `OrderService::place()` to assert bundle child stock deduction and bundle `meta` on `OrderItem`. Not an admin-orders test.

### `tests/Unit/ExternalCatalog/OrderExportServiceTest.php`

**External-catalog-owned.** Builds Elkos line payload from in-memory `Order`/`OrderItem` (bundle expansion). Does not dispatch `SubmitExternalOrderJob` or call admin `sendExternal`.

## Missing (observed)

- No tests under `tests/Feature` for `/admin/orders` or `/api/v1/me/orders`.
- No `Order` / `CreditNote` factories (`OrderShipmentAssignment` uses `HasFactory` with no factory class).
- `tests/Unit/Product/ProductPriceHistoryRecorderTest.php` is unrelated (filename match only).

## How to run the refund unit tests

```
php artisan test --filter=DiscountEligibilityAndRefundTest
```
