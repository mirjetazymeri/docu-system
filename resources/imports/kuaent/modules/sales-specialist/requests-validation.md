# Sales specialist — requests and validation

All FormRequests: `authorize()` returns `true`. Access is the route middleware `role:Sales Specialist`.

Address field presence is delegated to `App\Support\CheckoutAddressFields` (checkout settings `checkout.address_fields.*`). Exact billing/shipping rule matrices belong to [`../cart-checkout/requests-validation.md`](../cart-checkout/requests-validation.md); this page lists how this module **merges** them.

## Trait `App\Http\Requests\SalesSpecialist\Concerns\ValidatesSalesCustomer`

Used by `PreviewCartRequest`, `PreviewFulfillmentRequest`, `StoreOrderRequest`.

### `customerPlacementRules(bool $required): array`

| Field | Rules |
|---|---|
| `customer_mode` | `required` or `sometimes`, `in:existing,guest` |
| `customer_id` | `nullable`, `required_if:customer_mode,existing`, `integer`, `exists:users,id` |
| `guest_email` | `nullable`, `email`, `max:191` |

`exists:users,id` does **not** require the Customer role; that is `validateSalesCustomer`.

### `validateSalesCustomer(Validator $validator, bool $requireCustomer = true): void`

| Situation | Effect |
|---|---|
| Empty `customer_mode` and `$requireCustomer === false` | If `customer_id` filled, `assertCustomerRole`. Return. |
| `customer_mode === 'guest'` | If `!CheckoutAddressFields::guestCheckoutEnabled()`, error on `customer_mode`: `Guest checkout is disabled in Checkout Settings.` |
| `existing` or `customer_id` filled | `assertCustomerRole` |

### `assertCustomerRole(Validator $validator, int $customerId): void`

`User::find($customerId)` missing or `!$customer->hasRole('Customer')` → error on `customer_id`: `A valid customer account must be selected.`

## `StoreCustomerRequest`

| Field | Rules |
|---|---|
| `name` | `required`, `string`, `max:255` |
| `email` | `required`, `email`, `max:255`, `Rule::unique('users', 'email')` |
| `phone` | `nullable`, `string`, `max:50` |

`phone` is not persisted on `User`. See [controllers.md](controllers.md).

## Shared after-validation: duplicate products

`PreviewCartRequest`, `PreviewFulfillmentRequest`, and `StoreOrderRequest` add:

If `items.*.product_id` values are not unique → error on `items`: `Each product may only appear once. Adjust quantities instead.`

They do **not** re-check `Product::forChannel` or `status=active` (that happens in `prepareCart` via `firstOrFail`).

## `PreviewCartRequest`

### `prepareForValidation()`

- `customer_mode`: if not `existing`/`guest`, set to `existing` when `customer_id` is filled, otherwise leave unset.
- `same_as_billing`: merged as boolean only when the request **has** that key.

### `rules()`

| Field | Rules |
|---|---|
| `customer_mode` | `sometimes`, `in:existing,guest` |
| `customer_id` | `nullable`, `integer`, `exists:users,id` |
| `guest_email` | `nullable`, `email`, `max:191` |
| `items` | `required`, `array`, `min:1` |
| `items.*.product_id` | `required`, `integer`, `exists:products,id` |
| `items.*.qty` | `required`, `integer`, `min:1`, `max:9999` |
| `same_as_billing` | `sometimes`, `boolean` |
| `billing` | `sometimes`, `array` |
| `billing.email` | `nullable`, `email`, `max:191` |
| `shipping_method_id` | `sometimes`, `integer`, `exists:shipping_methods,id` |

Plus `CheckoutAddressFields::optionalBillingRules()` and `optionalShippingRules()`.

### `withValidator()`

`validateSalesCustomer($validator, requireCustomer: false)` + duplicate product check.

`shipping_method_id` existence is **not** scoped to the current channel here; `setShippingMethod` later uses `active()->forChannel`.

## `PreviewFulfillmentRequest`

### `prepareForValidation()`

- `customer_mode`: if not `existing`/`guest`, `existing` when `customer_id` filled, else **`guest`**.
- `same_as_billing`: always merged via `$this->boolean('same_as_billing')` (unchecked checkbox → `false`).

### `rules()`

`customerPlacementRules(true)` plus:

| Field | Rules |
|---|---|
| `items` / `items.*` | Same as preview cart |
| `same_as_billing` | `sometimes`, `boolean` |
| `billing` | `required`, `array` |
| `billing.email` | `nullable`, `email`, `max:191` |

Plus `CheckoutAddressFields::billingRules()` and `shippingRules()` (`shipping` required unless `same_as_billing` is true).

No `shipping_method_id` / `payment_method_id` on this request (options are the **output**).

### `withValidator()`

`validateSalesCustomer($validator)` (`requireCustomer` default **true**) + duplicate products.

## `StoreOrderRequest`

### `prepareForValidation()`

Same `customer_mode` default as fulfillment (`guest` when no `customer_id`). `same_as_billing` always boolean-merged.

### `rules()`

`customerPlacementRules(true)` plus:

| Field | Rules |
|---|---|
| `items` / `items.*` | Same as above |
| `shipping_method_id` | `required`, `integer`, `exists:shipping_methods,id` where `is_active` is true |
| `payment_method_id` | `required`, `integer`, `exists:payment_methods,id` where `is_active` is true |
| `customer_note` | `nullable`, `string`, `max:2000` |
| `same_as_billing` | `sometimes`, `boolean` |
| `billing` | `required`, `array` |
| `billing.email` | `nullable`, `email`, `max:191` |

Plus `CheckoutAddressFields::billingRules()` and `shippingRules()`.

### `withValidator()` after hooks

1. `validateSalesCustomer`.
2. Duplicate `product_id`.
3. `$channelId = (int) (app('currentChannel')?->id ?? 0)`.
4. `ShippingMethod::active()->forChannel($channelId)->find(shipping_method_id)` — missing → `Selected shipping method is not available.`
5. `PaymentMethod::active()->forChannel($channelId)->find(payment_method_id)` — missing → `Selected payment method is not available.`

Channel id `0` makes `forChannel` match nothing (`whereRaw('0 = 1')` on payment/shipping models). Redirect-driver exclusion is **not** in this request.

## Inline / other validation

| Location | What |
|---|---|
| `OrderController::index` | `per_page` clamped in PHP, not a FormRequest |
| `CustomerController::search` | `q` unvalidated string |
| `OrderController::searchProducts` | `q` / `ids` unvalidated |
| Unrouted search methods | `q` unvalidated |

JSON FormRequest failures use Laravel’s default **422** `{ message, errors }`. Service `RuntimeException` on preview endpoints is `{ message }` **422** without an `errors` object.
