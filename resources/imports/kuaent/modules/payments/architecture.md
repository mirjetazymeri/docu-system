# Payments architecture

## Layers

```text
Admin UI (payment_methods views)
        │
        ▼
PaymentMethodController + Store/UpdatePaymentMethodRequest
        │  validates driver alias + config.{field} rules
        ▼
PaymentGatewayRegistry  ←  config/payments.php
        │
        ├── driver_class       → PaymentDriverManager → PaymentDriver
        └── callback_handler   → PaymentCallbackController → PaymentCallbackHandler

Checkout / cart
        │
CartService::setPaymentMethod  →  driver.assertUsable + driver.initiate  →  cart_payments
CheckoutController::placeOrder
        ├── RedirectPaymentDriver → 202 payment_redirect_required (no order yet)
        └── otherwise (COD)       → OrderService::place → 201 + order_payments

Gateway browser / bank
        │
/api/v1/payments/{gateway}/return|notify|capture-browser-result
        │
Callback handler (lookup cart_payments.reference)
        ├── RaiAccept: verify Signature with bank_public_key_pem
        └── PCB Bank: GET /order/{id} over mutual TLS (source of truth)
        │
Finalize*OrderJob::dispatchSync
        ├── OrderService::place (idempotency_key = "{gateway}:{reference}")
        ├── order_payments status + gateway data blob
        └── optional MailService::sendPaymentConfirmation when send_invoice
```

## Registry

`PaymentGatewayRegistry` reads `config('payments.drivers')`. Each entry may include:

- `label`, `driver_class` (required for admin list and `PaymentDriverManager`)
- `callback_handler` (optional; only `raiaccept` and `pcbbank`)
- `fields[]` with `name`, `label`, `type`, `default`, `rules`, and optional `public`

`PaymentDriverManager::driverFor(PaymentMethod)` resolves `payment_methods.driver` through the registry. If the alias is unknown, it also accepts a stored FQCN that implements `PaymentDriver`. Unknown values throw `InvalidArgumentException`.

Callback resolution is stricter: `resolveCallbackHandlerClass` requires `callback_handler` to be a subclass of `PaymentCallbackHandler`. COD has no handler; `PaymentCallbackController` returns 404 (JSON) or a plain-text decline on notify.

## Two checkout shapes

`PaymentDriver` (`assertUsable`, `initiate`, `capture`, `cancel`) is implemented by all three drivers.

`RedirectPaymentDriver` (`gatewayRedirect`) is implemented only by `RaiAcceptDriver` and `PcbBankDriver`. `CheckoutController::placeOrder` checks `instanceof RedirectPaymentDriver` and returns HTTP 202 instead of placing the order. The shopper pays on the hosted page; the order is created later in the finalize job.

`CashOnDeliveryDriver` is not a redirect driver. Place-order runs `OrderService::place` immediately.

`capture()` is a no-op on all three drivers. `cancel()` sets `cart_payments.status` to `cancelled`. Neither method is called from checkout, callbacks, or jobs in this codebase.

## Channel and settings

API routes sit in `Route::prefix('v1')->middleware(['channel.api', 'locale.api'])`. `ResolveApiChannel` binds `currentChannel` from `?channel=`, `X-Channel`, the authenticated user’s current channel, or channel code `default`.

Admin payment methods are scoped with `PaymentMethod::forCurrentChannel()` (the admin user’s `current_channel_id`). Methods are attached to channels via `payment_method_channel`.

Guest usability uses `setting('system.checkout', true)` plus `payment_methods.allow_guest` (see [dependencies.md](dependencies.md)).

## Public vs secret config

`GET /api/v1/payment/methods` returns `config` from `PaymentGatewayRegistry::publicConfigFor`: only fields with `public => true` in `config/payments.php`. PEM keys, merchant IDs, gateway URLs used for signing, and notify URLs are not in that list.

## Status vocabulary

| Layer | Values used in this module |
| --- | --- |
| `cart_payments.status` | `pending`, `authorized`, `failed`, `cancelled` (COD comment also mentions `captured` for online payments; drivers here do not write `captured`) |
| PCB internal mapping (`PcbBankStatus`) | `authorized` / `failed` / `pending` |
| `order_payments.status` | constants: `pending`, `authorized`, `captured`, `failed`, `refunded`, `canceled` |
| Order after successful card finalize | `pending` → `processing`, `paid_at` set if empty |

Gateway-specific statuses (PCB TXPG `OrderStatus`, RaiAccept `TranCode`) are stored on payloads / `order_payments.data` and mapped before the cart/order rows are updated.
