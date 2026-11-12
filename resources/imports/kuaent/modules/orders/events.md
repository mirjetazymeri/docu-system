# Orders — events (observer)

There are **no** `app/Events/*` classes for orders. Side effects use `OrderObserver`.

## Registration

`app/Providers/EventServiceProvider.php`:

```
Order::observe(OrderObserver::class);
```

`shouldDiscoverEvents()` is `false`. `$listen` has no order events (only `Login` → `SendLoginAlert`).

## `App\Observers\OrderObserver`

Constructor: `MailService`.

All mail/job work is wrapped in `DB::afterCommit` so place-order transactions finish (items, payments, shipment exist) before mail snapshots and Elkos dispatch.

### `created(Order $order)`

1. `MailService::notifyAdminNewOrder($order)` — skipped unless `setting('notifications.admin.notify_new_order')`.
2. `MailService::sendOrderStatus($order)` — skipped unless the toggle for current status (on create this is `pending` → `notifications.order.placed`).
3. If `ExternalCatalogOrderSettings::isAuto()`: `SubmitExternalOrderJob::dispatch($order->id)`.

`isAuto()` / `isManual()` read setting `product.external_order.mode` for `config('catalog.default_channel_id', 1)` when no channel id is passed. The observer does **not** pass the cart’s channel id.

### `updated(Order $order)`

Returns immediately unless `wasChanged('status')`.

Then after commit:

- If status is `shipped`, `trackingNumber = $order->shipments()->latest()->value('tracking_number')` (`latest()` orders by `created_at` desc). Admin resend uses `latest('id')` instead.
- `MailService::sendOrderStatus($order, $trackingNumber)`.

Status changes that **bypass** `transitionTo()` still fire this observer if `status` was dirty, including:

- `FinalizePcbBankOrderJob` / `FinalizeRaiAcceptOrderJob` setting `pending` → `processing` via `$order->status = …; $order->save()`.
- Credit note moving the order to `refunded`.
- Assignment sync / shipment update calling `transitionTo`.

Saving other attributes (notes, external sync columns, `paid_at` without a status change) does **not** send status mail.

External export **on update** is not dispatched here. Manual send is admin `sendExternal`. Auto send is create-only.

## Mail vs observer

Observer never attaches invoices. Payment confirmation + PDF is `MailService::sendPaymentConfirmation()`, called from finalize jobs when `PaymentMethod.send_invoice` is true — not from this observer.

Admin `resendConfirmation` calls `MailService::resendOrderConfirmation()` directly (no observer).
