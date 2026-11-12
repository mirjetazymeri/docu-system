# Payments tests

Searched `tests/` for `PaymentMethod`, `CartPayment`, `OrderPayment`, `RaiAccept`, `PcbBank`, `CashOnDelivery`, `payment/methods`, `payments/`, `FinalizeRai`, `FinalizePcb`.

## Dedicated payment tests

There are **no** feature or unit tests for:

- Admin payment-method CRUD
- `GET /api/v1/payment/methods`
- Callbacks (`return` / `notify` / `status` / `capture-browser-result`)
- `CashOnDeliveryDriver`, `RaiAcceptDriver`, `PcbBankDriver`, `PcbBankClient`, signatures
- `FinalizeRaiAcceptOrderJob` / `FinalizePcbBankOrderJob`
- `PaymentGatewayRegistry` / `PaymentDriverManager`

## Checkout fixture that creates payment rows

`tests/Feature/Bundle/BundleCartCheckoutTest.php` helper `placeOrder()` creates:

- `PaymentMethod` with `code` `cod` and **`driver` `cash_on_delivery`** (not the registry alias `cod`)
- `CartPayment` `pending` with that method
- Then `OrderService::place`

That path never calls `PaymentDriverManager` or `CashOnDeliveryDriver`. It exists so bundle stock tests can place an order.

No other file under `tests/` references these payment models.
