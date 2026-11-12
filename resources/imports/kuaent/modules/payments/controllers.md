# Payments controllers

## Admin: `App\Http\Controllers\Admin\PaymentMethodController`

Permissions (constructor middleware):

| Permission | Actions |
| --- | --- |
| `payment_method_view` | `index` |
| `payment_method_create` | `create`, `store` |
| `payment_method_edit` | `edit`, `update` |
| `payment_method_delete` | `delete` |

Injects `PaymentGatewayRegistry`.

| Method | Behavior |
| --- | --- |
| `index` | Non-AJAX: view `admin.payment_methods.index` with search `q`. AJAX: DataTables on `PaymentMethod::forCurrentChannel()->with('channels')`; filters `name`/`code`; columns for channels, guest, active, min/max, excluded countries, driver, actions. |
| `create` | View `admin.payment_methods.create` with `availableDrivers()` and `all()` schemas. |
| `store` | `StorePaymentMethodRequest`; `PaymentMethod::create`; `channels()->sync` from `channels` or `[auth()->user()->current_channel_id]`; redirect index. |
| `edit` | `findOrFail` with channels; same driver lists; `assignedChannels`. |
| `update` | `UpdatePaymentMethodRequest`; update + sync channels; `back()`. |
| `delete` | `findOrFail` then `delete()`; redirect index. |

Views: `resources/views/admin/payment_methods/{index,create,edit,_form,actions}.blade.php`. `_form` renders `config[field]` inputs from each driver’s `fields` in `config/payments.php`.

## API: `App\Http\Controllers\Api\V1\PaymentMethodController`

`GET /api/v1/payment/methods`.

- Requires `currentChannel` (else 400 `Channel not resolved.`).
- Loads `PaymentMethod::active()->forChannel($channelId)` ordered by `sort_order`, `id`.
- Drops methods whose `countries` exclude the cart shipping/billing country.
- Cart resolution: Sanctum user → latest active user cart for the channel, or creates one (`currency` `EUR`); guest → `X-Cart-Token` or `cart_token` query (or a new UUID), same create-if-missing behavior.
- Country for exclusion: `cart.shipping.country_code` / `country`, else billing. Empty excluded list or missing country → method is kept.
- Response: `{ data: [ { id, code, name, driver, sort_order, min_total, max_total, allow_guest, config } ] }` where `config` is `publicConfigFor`.

## API: `App\Http\Controllers\Api\V1\PaymentCallbackController`

Injects `PaymentGatewayRegistry`. Gateway segment is `strtolower(trim($gateway))`. Handler from `resolveCallbackHandlerClass` + `app($class)` must implement `PaymentCallbackHandler`.

| Action | HTTP | Behavior |
| --- | --- | --- |
| `handleReturn` | GET, POST | Logs return. `process($request, 'return')`. If `redirect` is non-empty, `redirect()->away`. Else JSON body with handler status. Unknown gateway: JSON 404. |
| `captureBrowserResult` | POST | Same `process(..., 'return')` but **always JSON** (redirect URL in the handler result is ignored). |
| `handleNotify` | GET, POST | `process(..., 'notify')`. If `notify_text` is set, HTTP **200** `text/plain` (or handler `notify_content_type`) regardless of handler `status`. Else JSON. Unknown gateway: 200 text/plain `Response.action=decline` / `Response.reason=unsupported_gateway`. |
| `status` | GET | Query `gateway_order_id` required (422 if missing). Finds `CartPayment` by `reference`. 404 `status: not_found` if missing or `paymentMethod.driver !== $gateway`. 200: `status`, `gateway`, `gateway_order_id`, `cart_id`, `order_id` (`cart.order_id`). |

No Sanctum requirement on these actions. No throttle on the callback routes (place-order throttle is on checkout, not here).

## Related controllers (not under `Admin/PaymentMethod` / `Api/V1/Payment*`)

These call payment drivers or persist `OrderPayment`; they belong to cart-checkout / orders.

### `App\Http\Controllers\Api\V1\CartController::setPaymentMethod`

Validates `SetPaymentMethodRequest`. Resolves cart (Sanctum / `X-Cart-Token`). `CartService::setPaymentMethod`. Returns `CartResource`.

### `App\Http\Controllers\Api\V1\CheckoutController::placeOrder`

If the cart’s payment method is active on the channel and its driver is `RedirectPaymentDriver`, returns `202` `{ data: gatewayRedirect(...) }` or `422` on `RuntimeException`. Otherwise `OrderService::place` → `201` `OrderResource`, with optional `Idempotency-Key` cache (`checkout:idem:{cartId}:{key}`, 6 hours). Throttle `20,1` on the route.

### `App\Http\Controllers\Admin\OrderController`

- `markPaid`: creates `OrderPayment` `captured` with `data.marked_by = admin` (not a gateway capture).
- `updatePaymentStatus`: blocked when `OrderPayment::isGatewayManaged()`.
- Order show loads `PaymentMethod::forCurrentChannel()` for the mark-paid UI.

### `App\Http\Controllers\SalesSpecialist\OrderController`

- `searchPaymentMethods` filters out `RedirectPaymentDriver` (and falls back to any active channel methods if the filtered list is empty). **No matching route** in `routes/web.php` or `routes/api.php`.
- Fulfillment preview uses `SalesSpecialistOrderService` (same redirect-driver exclusion).
