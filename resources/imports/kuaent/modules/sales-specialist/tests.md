# Sales specialist — tests

Searched `tests/` for `SalesSpecialist`, `sales-specialist`, `sales_specialist`, `SOURCE_SALES_SPECIALIST`, `ownedBySalesSpecialist`, and `CustomerController` (sales namespace).

## Dedicated coverage

There are **no** Feature or Unit tests for:

- `App\Http\Controllers\SalesSpecialist\*`
- `App\Services\SalesSpecialist\SalesSpecialistOrderService`
- `App\Http\Requests\SalesSpecialist\*`
- Routes under `/sales-specialist/*`
- `ResolvesSalesChannel`

No factories or HTTP tests assert `orders.source = sales_specialist` or `sales_specialist_id`.

## Adjacent tests (helpers this module calls)

These do **not** hit the sales desk HTTP layer.

| File | What it covers |
|---|---|
| `tests/Unit/CheckoutAddressFieldsTest.php` | `CheckoutAddressFields` billing/shipping rule flags from `config/settings` defaults (postcode, label, guest, etc.). Same helper used by specialist FormRequests and Blade. |
| `tests/Unit/PersonNameTest.php` | `PersonName::split` (PHPUnit `TestCase`, no Laravel app). Used by `OrderController::create` billing prefill. |
| `tests/Feature/Bundle/BundleCartCheckoutTest.php` | `OrderService::place()` for bundle stock. Checkout-owned; does not pass `source` / `sales_specialist_id`. |

## Missing (observed)

- No test that guest vs existing customer modes match `ValidatesSalesCustomer`.
- No test that redirect payment methods are omitted from `previewFulfillment`.
- No test that `CheckoutController::placeOrder` leaves `orders.source` null while `placeForCustomer` sets `sales_specialist`.
- No test for `authorizeOwnership` 403 vs list scoping.
