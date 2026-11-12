# Orders — jobs

This module **does not own job classes**. It **dispatches** one job from `OrderObserver`. Payment finalize jobs **mutate** orders; they are documented as inbound dependencies.

## Dispatched here

### `App\Jobs\ExternalCatalog\SubmitExternalOrderJob`

**Owner:** [external-catalog](../external-catalog/).

**Dispatch:** `OrderObserver::created` when `ExternalCatalogOrderSettings::isAuto()`.

```
SubmitExternalOrderJob::dispatch($order->id);
```

| Property | Value |
|---|---|
| Queue | `(string) config('external_catalog.order_queue', 'imports')` — env `EXTERNAL_CATALOG_ORDER_QUEUE` falling back to `EXTERNAL_CATALOG_IMPORT_QUEUE`, default `imports` |
| `$tries` | 3 |
| `$timeout` | 60 |
| Horizon | `supervisor-import` watches `imports` (`config/horizon.php`) |

`handle()`: if mode is no longer auto, return. Missing order → return. If `external_order_sync_status === OrderExportService::STATUS_SYNCED` (`synced`) → return. Else `OrderExportService::send($order)`. Failures are logged; the job does not throw from a failed `success` flag (exporter catches and writes `failed` on the order).

Admin manual send does **not** use this job; it calls `OrderExportService::send()` in-process.

---

## Inbound: payments finalize jobs (not dispatched by orders)

Owner: [payments](../payments/). Classes:

- `App\Jobs\FinalizePcbBankOrderJob`
- `App\Jobs\FinalizeRaiAcceptOrderJob`

Shared order mutations (when gateway reports success):

1. Resolve/create order via `OrderService::place($cart, ['idempotency_key' => '{gateway}:{cartPayment reference or id}'])` if not already linked (`cart.order_id` or existing idempotency key).
2. Upsert/update latest `OrderPayment`: status `authorized` (`OrderPayment::STATUS_AUTH`), amount = `order.grand_total`, `reference` from cart payment, `data.pcbbank` or `data.raiaccept` callback history.
3. If order `status === pending`: set `status = processing`, set `paid_at` if empty, `save()` — **does not call `transitionTo()`**. Observer still sees a status change → processing mail (if `notifications.order.processing` is on; default for that setting is **false** in `config/settings.php`).
4. If `payment_methods.send_invoice`: `MailService::sendPaymentConfirmation()` (invoice PDF + `PaymentConfirmationMail`).

On gateway failure: if an order already exists, payment row is set to `failed`. PCB job does not place an order on failure. Neither job transitions the order to `canceled`.

COD / non-redirect methods finalize at checkout place time (`OrderPayment` starts `pending`); they do not use these jobs.

---

## Not order jobs

| Job | Owner |
|---|---|
| `RebuildProductProjectionJob` | catalog-products (triggered after rental return via `ProductProjectionService::rebuild`, not a dispatch from OrderController itself) |
| Catalog reindex jobs | catalog-rules-pricing |
