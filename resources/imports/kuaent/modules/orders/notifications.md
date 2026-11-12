# Orders — notifications (mail)

Orders do **not** use `app/Notifications/*`. That folder only has password reset and email verification.

Customer/admin order alerts are Laravel **Mailables**, sent through `App\Services\Mail\MailService` ([shared mail](../../shared/README.md#mail)). Order mailables use the `Queueable` trait but `MailService` sends them with `Mail::to()->send()` (**synchronous**), except `resendOrderConfirmation` which also uses `send()` and rethrows on failure.

Payloads are arrays from `MailPayloadBuilder::order()` so queued copies would not need Eloquent (status mail is still sent now).

## Mailables owned with this module

### `App\Mail\Order\OrderStatusMail`

- View: `emails.order.status`
- From: `notifications.mail.from_address` / `from_name` (fallback `config('mail.from.*')`)
- Subject: `Order #{number} — {Ucfirst status} | {store.company_name or app.name}`
- Constructor: `array $orderDetails`, `?string $trackingNumber`
- Tracking block in the view only if status is `shipped` and tracking is non-empty

### `App\Mail\Order\PaymentConfirmationMail`

- View: `emails.order.payment-confirmation`
- Subject: `Payment Confirmation — Order #{number} | {site}`
- Constructor: order details, `receipt` array (`PaymentReceipt::toArray()`), optional absolute `$invoicePath`
- Attaches `INV-{number}.pdf` when `$invoicePath` is a readable file

### `App\Mail\Admin\NewOrderMail`

- View: `emails.admin.new-order`
- Envelope **to**: `notifications.admin.email` (fallback `mail.from.address`)
- Subject: `[Admin] New Order #{number}`

## `MailService` methods

| Method | When | Toggle |
|---|---|---|
| `notifyAdminNewOrder` | Observer `created` | `notifications.admin.notify_new_order` |
| `sendOrderStatus` | Observer create + status change | `notifications.order.{mapped}` |
| `resendOrderConfirmation` | Admin resend | **None** (bypasses toggles). Throws if no email or send fails. |
| `sendPaymentConfirmation` | Payments finalize jobs if `send_invoice` | No `notifications.order.*` check. Generates invoice via `InvoiceService`. Skips if no customer email. |

Status map inside `sendOrderStatus`:

| `orders.status` | Setting key |
|---|---|
| `pending` | `notifications.order.placed` |
| `processing` | `notifications.order.processing` |
| `paid` | `notifications.order.confirmed` |
| `shipped` | `notifications.order.shipped` |
| `completed` | `notifications.order.delivered` |
| `canceled` | `notifications.order.cancelled` |
| `refunded` | `notifications.order.refunded` |

Defaults in `config/settings.php`: placed/confirmed/shipped/delivered/cancelled/refunded default **true**; `processing` default **false**.

`notifications.order.invoice_attached` exists in settings (label: attach invoice PDF to confirmation) but **no PHP references** it. Status mail does not attach a PDF. Invoice attach happens only on `PaymentConfirmationMail`.

Recipient for customer mails: `order.email` then `order.user->email`. Missing email: `sendOrderStatus` / `sendPaymentConfirmation` log and skip; resend throws.

`MailPayloadBuilder::order()` includes number, email, `customer_name` (user name, else billing/shipping first+last, else `Guest`), status, currency, totals, `placed_at` / `placed_at_date`, item name/sku/qty/prices, `shipping_address` data.

## Admin UI

`admin.orders.resend.confirmation` → `resendConfirmation`. Success flash “Confirmation email sent.”
