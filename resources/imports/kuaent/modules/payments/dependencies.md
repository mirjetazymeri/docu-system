# Payments dependencies

Verified from code. Payments does not own cart, order, or settings tables; it calls into those modules.

## cart-checkout

| Touch point | How |
| --- | --- |
| `CartService::setPaymentMethod` | Injects `PaymentDriverManager`; channel-active method; excluded countries; `recalcTotals`; `assertUsable` + `initiate` in a DB transaction |
| `Cart::payment()` | `hasOne(CartPayment)` |
| `CartController::setPaymentMethod` | API attach |
| `CheckoutController::placeOrder` | `RedirectPaymentDriver` → 202; else `OrderService::place` |
| `PlaceOrderRequest` | Terms/email only; payment comes from cart |
| Guest email | `persistGuestEmail` before redirect so finalize mails have `cart.email` |

Checkout cannot place a redirect payment until `initiate` stored `gateway_url` / HPP `redirect_url` and `reference`.

## orders

| Touch point | How |
| --- | --- |
| `OrderService::place` | Requires a channel-active payment method on the cart; creates `OrderPayment` `pending` with `data` = cart payment array; used by COD place-order and by both finalize jobs |
| `OrderObserver::created` | After COD (and after card finalize place): admin new-order + customer status mail |
| Finalize jobs | Idempotency `{raiaccept\|pcbbank}:{reference}`; set order `processing` + `paid_at`; sync `OrderPayment` `authorized` / `failed` |
| `Order::payments` / `payment()` / `totalPaid()` | `authorized` counts as paid |
| `Admin\OrderController` | `markPaid`, `updatePaymentStatus` (`isGatewayManaged` blocks PSP rows) |
| `CreditNoteService` | Creates negative `OrderPayment` `refunded` (orders/credit-notes, not gateway capture) |
| `InvoiceService` / `MailService::sendPaymentConfirmation` | `PaymentReceipt` from `order_payments.data` when `send_invoice` |

## settings

| Touch point | How |
| --- | --- |
| `setting('system.checkout', true)` | All three drivers’ `assertUsable`. Key defined in `config/settings.php` as `system.checkout` (“Checkout Guest Enabled”, default true, public). Guest + `!allow_guest` is rejected only when this setting is **false**. |
| `setting()` helper | `app/helpers.php` → `SettingService::get` |
| `config/settings.php` `checkout.guest_enabled` | Exists in settings schema; **payment drivers do not read it** |
| Per-method secrets/URLs | Stored on `payment_methods.config`, not the settings table |
| Env defaults | Names only: `APP_URL`, `RAIACCEPT_RETURN_URL`, `RAIACCEPT_RESULT_URL`, `RAIACCEPT_NOTIFY_URL`, `PCB_BANK_BASE_URL`, `PCB_BANK_ORDER_TYPE`, `PCB_BANK_RETURN_URL`, `PCB_BANK_RESULT_URL` |

Invoice PDF store fields (`store.*`, `general.logo`) are read when generating the payment-confirmation attachment; that is invoice/mail, triggered from finalize when `send_invoice` is on.

## Other verified couplings

| Area | How |
| --- | --- |
| Channels | `payment_method_channel`; API `currentChannel`; jobs re-bind channel |
| Countries | Admin excluded-country list; API/cart filter |
| Sales specialist | Non-redirect methods only in fulfillment preview; `CartService::setPaymentMethod` then `OrderService::place` |
| HTTP | `Illuminate\Support\Facades\Http` in `PcbBankClient` |

No other payment driver packages or aliases appear in `config/payments.php`.
