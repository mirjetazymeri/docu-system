# Payments

Payment method administration, gateway drivers (COD, RaiAccept, PCB Bank), storefront/API callbacks, and order-finalization jobs.

Registered driver aliases in `config/payments.php`:

| Alias | Label | Driver class | Callback handler |
| --- | --- | --- | --- |
| `cod` | Cash on Delivery | `App\Services\Payment\Drivers\CashOnDeliveryDriver` | none |
| `raiaccept` | RaiAccept | `App\Services\Payment\Drivers\RaiAccept\RaiAcceptDriver` | `App\Services\Payment\Callbacks\RaiAcceptCallbackHandler` |
| `pcbbank` | PCB Bank (Card) | `App\Services\Payment\Drivers\PcbBank\PcbBankDriver` | `App\Services\Payment\Callbacks\PcbBankCallbackHandler` |

No other payment drivers are registered.

## What this module does

- Admin CRUD for `payment_methods` (channel assignment, excluded countries, guest/invoice flags, per-driver `config` JSON).
- Checkout listing of active methods (`GET /api/v1/payment/methods`) with public config only.
- Cart attachment (`POST /api/v1/cart/payment/method`) via `CartService` + `PaymentDriver::initiate`.
- Place-order split: COD places immediately (`201`); redirect drivers return `202 payment_redirect_required`.
- Gateway return/notify/status/capture routes under `/api/v1/payments/{gateway}/…`.
- Synchronous finalize jobs that call `OrderService::place` after a successful card payment.

## Docs in this folder

| File | Contents |
| --- | --- |
| [architecture.md](architecture.md) | Layers, registry, redirect vs COD |
| [models.md](models.md) | `PaymentMethod`, `CartPayment`, `OrderPayment` |
| [database.md](database.md) | Tables, columns, FKs, indexes |
| [controllers.md](controllers.md) | Admin + API controllers (and related callers) |
| [services.md](services.md) | Registry, drivers, callbacks, receipt |
| [jobs.md](jobs.md) | `FinalizeRaiAcceptOrderJob`, `FinalizePcbBankOrderJob` |
| [requests-validation.md](requests-validation.md) | Form requests and driver field rules |
| [routes.md](routes.md) | Admin and API routes |
| [api.md](api.md) | Storefront/API contracts |
| [workflows.md](workflows.md) | Capture / notify / finalize (Mermaid) |
| [dependencies.md](dependencies.md) | cart-checkout, orders, settings (verified) |
| [tests.md](tests.md) | What `tests/` covers |

## Secrets

Driver credentials live in `payment_methods.config` (admin form), not in `.env` except for URL/base defaults. This documentation lists **environment variable names only**. Do not copy PEM material from `pcb bank payment doc/` or from the admin form.
