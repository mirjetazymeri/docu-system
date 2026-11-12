# Payments jobs

Both jobs implement `ShouldQueue` (`Dispatchable`, `InteractsWithQueue`, `Queueable`, `SerializesModels`) but **callers use `dispatchSync`**. Comments in the callback handlers: order + mails must not depend on `queue:work` (COD places inline).

Neither job defines `$tries`, `$timeout`, or `$backoff`. There is no retry/timeout configuration in these classes.

Channel: `bindChannelContext` sets `app()->instance('currentChannel', …)` from `cart.channel_id` so `OrderService` sees the same channel as checkout.

## `FinalizeRaiAcceptOrderJob`

**File:** `app/Jobs/FinalizeRaiAcceptOrderJob.php`

**Constructor:** `int $cartPaymentId`, `array $callbackPayload`, `bool $verified`, `string $source`.

**Dispatched from:** `RaiAcceptCallbackHandler::process` (`dispatchSync`).

**Handle:** `OrderService`, `MailService`.

1. Load `CartPayment` with `cart`, `paymentMethod`. Return if missing cart or `driver !== 'raiaccept'`.
2. Success iff `callbackPayload['TranCode'] === '000'`.
3. On failure: if `cart.order_id` already points at an order, sync `OrderPayment` `failed`; append cart callback `failed`; return (does not place).
4. On success: idempotency key `raiaccept:{reference|id}`. Reuse `cart.order_id` or existing order with that key; else `OrderService::place($cart, ['idempotency_key' => …])`.
5. Sync `OrderPayment` `authorized` and merge `data.raiaccept` (`gateway_order_id`, `tran_code`, `approval_code`, `rrn`, `xid`, `currency`, `amount`, `verified`, `callbacks[]`, `raw`).
6. If order status is `pending`: set `processing`, set `paid_at` if empty; if `payment_methods.send_invoice`, `MailService::sendPaymentConfirmation`.
7. Append cart payment `finalization` and set cart status `authorized`.

## `FinalizePcbBankOrderJob`

**File:** `app/Jobs/FinalizePcbBankOrderJob.php`

**Constructor:** `int $cartPaymentId`, `array $orderData` (raw TXPG getOrder body), `string $gatewayStatus`, `bool $verified`, `string $source`.

**Dispatched from:** `PcbBankCallbackHandler::process` (`dispatchSync`).

**Handle:** `OrderService`, `MailService`.

1. Load payment; return if missing cart or `driver !== PcbBankDriver::GATEWAY` (`pcbbank`).
2. Success iff `PcbBankStatus::isSuccess($gatewayStatus)`.
3. On failure: if an order already exists on the cart, sync `OrderPayment` `failed`; return.
4. On success: idempotency `pcbbank:{reference|id}`; place if needed; sync `authorized` with `data.pcbbank` (`gateway_order_id`, `gateway_status`, `verified`, `callbacks[]`, `order` from `$orderData['order']`).
5. Same pending → processing / `paid_at` / optional payment-confirmation mail as RaiAccept.

PCB job does **not** append a `finalization` block on `cart_payments` (the handler already set cart `status` via `PcbBankStatus::toInternal`).
