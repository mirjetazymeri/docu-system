# Payments models

## `App\Models\PaymentMethod`

Table: `payment_methods`.

**Fillable:** `code`, `name`, `driver`, `is_active`, `allow_guest`, `send_invoice`, `countries`, `config`, `min_total`, `max_total`, `sort_order`.

**Casts:** booleans `is_active`, `allow_guest`, `send_invoice`; `countries` and `config` arrays; `min_total` / `max_total` `decimal:2`; `sort_order` integer.

**Relations**

- `channels()` — `belongsToMany(Channel::class, 'payment_method_channel')`. Inverse: `Channel::paymentMethods()`.

**Scopes**

- `active()` — `is_active = true`.
- `forCurrentChannel()` — `whereHas` channels matching `auth()->user()->current_channel_id`.
- `forChannel(?int $channelId)` — same `whereHas` on `channels.id`. If `$channelId` is empty, the query is `whereRaw('0 = 1')` (no rows).

**`countries`:** JSON array of ISO country codes treated as **excluded** destinations (admin label “excluded countries”). Listing and `CartService::setPaymentMethod` compare against cart shipping/billing country.

**`driver`:** registry alias (`cod`, `raiaccept`, `pcbbank`) or a `PaymentDriver` FQCN.

**`send_invoice`:** when true, finalize jobs call `MailService::sendPaymentConfirmation` after a successful card payment.

## `App\Models\CartPayment`

Table: `cart_payments`. One row per cart (`unique(cart_id)`). `Cart::payment()` is `hasOne(CartPayment)`.

**Fillable:** `cart_id`, `payment_method_id`, `status`, `amount`, `reference`, `payload`.

**Casts:** `payload` array, `amount` `decimal:2`.

**Relations:** `cart()`, `paymentMethod()`.

**`reference`:** gateway order id. RaiAccept: `ORD{cartId}{YmdHis}`. PCB Bank: TXPG `order.id` from `POST /order`. Callbacks and `GET …/status` look up `where('reference', $gatewayOrderId)->latest('id')`.

**`payload` (written by drivers / handlers)**

COD:

- `note` — `Collect cash on delivery`

RaiAccept (initiate):

- `gateway` — `raiaccept`
- `gateway_url`
- `request` — form fields posted to the bank (`Version`, `MerchantID`, `TerminalID`, `PurchaseTime`, `OrderID`, `Currency`, `TotalAmount`, `PurchaseDesc`, `locale`, `Signature`, optional `SUCCESS_URL` / `FAILURE_URL` / `NOTIFY_URL`)
- later: `callbacks[]`, `last_callback`, `finalization` (job)

PCB Bank (initiate):

- `gateway` — `pcbbank`
- `gateway_order_id`, `merchant_reference`, `password`, `hpp_url`, `redirect_url`, `currency`, `amount`, `gateway_status`, `request`
- later: `callbacks[]`, `last_callback`

`CartResource` serializes the `payment` relation as the model (including `payload`).

## `App\Models\OrderPayment`

Table: `order_payments`. `Order::payments()` is `hasMany`; `Order::payment()` is `hasOne()->latestOfMany()`.

**Constants**

| Constant | Value |
| --- | --- |
| `STATUS_PENDING` | `pending` |
| `STATUS_AUTH` | `authorized` |
| `STATUS_CAPTURED` | `captured` |
| `STATUS_FAILED` | `failed` |
| `STATUS_REFUNDED` | `refunded` |
| `STATUS_CANCELED` | `canceled` |

**Fillable:** `order_id`, `payment_method_id`, `method_code`, `method_name`, `status`, `amount`, `currency`, `reference`, `data`.

**Casts:** `amount` `decimal:2`, `data` array.

**Relations:** `order()`, `paymentMethod()`.

**`OrderService::place`** creates a row with `status` `pending` and `data` = `(array) $cart->payment`. Finalize jobs then set `authorized` or `failed` and merge gateway blocks under `data.raiaccept` or `data.pcbbank`.

**`isManuallyManaged()`:** true when `data.marked_by === 'admin'` or `data.refunded_by === 'admin'`.

**`isGatewayManaged()`:** false if manually managed. True if `data` has `pcbbank`, `raiaccept`, or a non-empty `gateway` key; otherwise true when the related method’s registry entry has a `callback_handler`. Admin `updatePaymentStatus` refuses gateway-managed rows.

`Order::totalPaid()` counts `order_payments` with `amount > 0` and status in `captured`, `authorized`, or `paid`. Finalize jobs write `authorized` (not `captured`).
