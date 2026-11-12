# Sales specialist — services

This module owns one service: `App\Services\SalesSpecialist\SalesSpecialistOrderService`.

Checkout internals (`CartService`, `OrderService`) are documented in [`../cart-checkout/services.md`](../cart-checkout/services.md). This page lists **entry points and arguments**, not a full rewrite of place-order.

## `SalesSpecialistOrderService`

Constructor:

| Dependency | Module |
|---|---|
| `CartService $carts` | [`../cart-checkout/README.md`](../cart-checkout/README.md) |
| `OrderService $orders` | [`../cart-checkout/README.md`](../cart-checkout/README.md) |
| `PaymentDriverManager $paymentDrivers` | [`../payments/README.md`](../payments/README.md) |
| `TaxService $tax` | [`../tax/README.md`](../tax/README.md) |

No other class under `app/Services/SalesSpecialist/`.

### `previewCart(?User $customer, array $data): array`

1. `ensureChannelBound()`.
2. `prepareCart($customer, $data)`.
3. If `shipping_method_id` is non-empty: `CartService::setShippingMethod($cart->fresh(), (int) id)` then refresh.
4. `CartService::recalcTotals($cart->load('items'))`.
5. `buildPreviewResponse($cart->fresh()->load('items.product'))`.

### `previewFulfillment(?User $customer, array $data): array`

1. `ensureChannelBound()`.
2. `prepareCart` + `load('items.product')`.
3. `$shippingQuotes = CartService::shippingQuotes($cart)` (storefront quote shape: `methodCode`, `methodName`, `amount`, `etaText`, `serviceLevel`, `meta`, `method_id`, `id`).
4. `PaymentMethod::active()->forChannel((int) $cart->channel_id)` ordered by `sort_order`, `id`. Keep a method only if `PaymentDriverManager::driverFor` succeeds, the driver is **not** `RedirectPaymentDriver`, and `assertUsable($method, $cart)` does not throw. Map to `{ id, code, name }`.
5. Return `buildPreviewResponse($cart)` merged with `shipping_quotes` and `payment_methods`.

Unlike the unrouted `OrderController::searchPaymentMethods`, this method has **no** fallback to redirect drivers when the filtered list is empty.

### `buildPreviewResponse(Cart $cart): array` (protected)

Loads `items.product` if missing. `$taxQuote = TaxService::calculate($cart)`.

| Key | Source |
|---|---|
| `items[]` | `product_id`, `sku`, `name`, `qty`, `unit_price`, `row_total` from cart items |
| `totals` | cart `subtotal`, `discount_total`, `tax_total`, `shipping_total`, `grand_total`, `currency` |
| `tax_breakdown` | `$taxQuote['breakdown'] ?? []` |
| `price_includes_tax` | `(bool) tax_config('price_includes_tax', true)` |

### `placeForCustomer(?User $customer, array $data): Order`

`ensureChannelBound()`, then `DB::transaction`:

1. `$cart = buildCart($customer, $data)`.
2. `$guestEmail = resolveGuestEmail($customer, $data)`.
3. `OrderService::place($cart, [ ... ])`.
4. Return `$order->fresh(['user', 'items', 'addresses', 'payments', 'shipments'])`.

Place opts (only this caller sets the last two):

| Opt | Value |
|---|---|
| `email` | `$guestEmail` (customer email, or guest candidates, or `null`) |
| `customer_note` | `$data['customer_note'] ?? null` |
| `idempotency_key` | `'sales-specialist:' . Auth::id() . ':' . Str::uuid()` |
| `sales_specialist_id` | `Auth::id()` |
| `source` | `Order::SOURCE_SALES_SPECIALIST` (`sales_specialist`) |

`CheckoutController::placeOrder()` does **not** pass `source` or `sales_specialist_id`. See [architecture.md](architecture.md).

`OrderService::place()` still runs its full checkout rules (active cart, stock, price match, tax snapshot, payment/shipment rows, cart → `ordered`). Failures surface as `RuntimeException` and become form/`422` errors in the controller.

### `buildCart(?User $customer, array $data): Cart` (protected)

`prepareCart`, then:

- `CartService::setShippingMethod($cart->fresh(), (int) $data['shipping_method_id'])`
- `CartService::setPaymentMethod($cart->fresh(), (int) $data['payment_method_id'])`

`setPaymentMethod` initiates the driver (COD `initiate`, country exclusion, `assertUsable`). Redirect drivers should not appear if the UI only listed `previewFulfillment` methods; the FormRequest does not itself reject `RedirectPaymentDriver`.

### `prepareCart(?User $customer, array $data): Cart` (protected)

Token: `'sales-specialist:' . Auth::id()`. Channel: `channelId()`.

Reuse or create scratch cart (see [architecture.md](architecture.md)).

Then:

1. `resolveGuestEmail` → `CartService::setCustomer($cart, $customer?->id, $guestEmail)`. Guest + guest checkout disabled throws `RuntimeException` from **CartService** (`Guest checkout is disabled.`) — wording differs from this service’s own guest check (`Guest checkout is disabled in Checkout Settings.`).
2. For each `$data['items']`: `Product::forChannel($channelId)->where('id', $item['product_id'])->where('status', 'active')->firstOrFail()` then `CartService::addItem($cart->fresh(), $product->id, (int) $item['qty'])`. Options/meta/rental are **not** passed (sale lines only). Configurable parents and rental-only products still fail inside `addItem` with checkout’s messages.
3. If `billing.country` is non-empty: copy billing; if guest and `$guestEmail`, set `billing['email']`. Shipping = billing when `same_as_billing` is missing or true, else `$data['shipping'] ?? $billing`. `CartService::setAddresses`.

Duplicate SKUs: FormRequests reject duplicate `product_id`. If two lines share a product id, `addItem` would merge qty on a matching sale line (checkout behavior).

### `resolveGuestEmail(?User $customer, array $data): ?string` (protected)

- Customer present → `$customer->email`.
- Else if guest checkout disabled → `RuntimeException` `Guest checkout is disabled in Checkout Settings.`
- Else first non-empty trimmed string among `$data['guest_email']` and `data_get($data, 'billing.email')`.
- Else `null`.

### `channelId(): int` / `ensureChannelBound(): void` (protected)

`channelId()` requires a bound channel with `id` or throws `No channel available for order placement.`

`ensureChannelBound()`: if already bound with id, return. Else `Auth::user()->currentChannel ?? channels()->first() ?? Channel::orderBy('id')->first()`. None → `No channel available.` Persist `current_channel_id` + `syncWithoutDetaching` when the user had none. Bind `app('currentChannel')`.

## Checkout call-outs (not owned here)

| Call | Why this module uses it |
|---|---|
| `CartService::setCustomer` | Attach existing customer or guest email |
| `CartService::addItem` | Sale qty; pricing via `effectiveUnitPrice` (catalog rule prices) |
| `CartService::setAddresses` | Billing/shipping JSON on the cart |
| `CartService::recalcTotals` | Tax + discounts onto cart columns |
| `CartService::shippingQuotes` | Channel methods, `frontend_visible`, country exclusion, driver `quote()` |
| `CartService::setShippingMethod` | Persist method + amount onto `cart.shipping` |
| `CartService::setPaymentMethod` | `CartPayment` + driver `initiate` |
| `OrderService::place` | Create `Order` + items/addresses/payments/shipments; deduct stock |
| `TaxService::calculate` | Preview `tax_breakdown` (place recalculates again inside `OrderService`) |
| `PaymentDriverManager::driverFor` | Skip `RedirectPaymentDriver`; `assertUsable` |

Coupons, rental holds, and customization uploads are **not** invoked from this service.
