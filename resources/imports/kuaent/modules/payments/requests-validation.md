# Payments requests and validation

## Admin store / update

`App\Http\Requests\Admin\PaymentMethod\StorePaymentMethodRequest`  
`App\Http\Requests\Admin\PaymentMethod\UpdatePaymentMethodRequest`

`authorize()`: `true`.

`prepareForValidation`:

- Booleans: `is_active`, `allow_guest`, `send_invoice`
- `sort_order` int (default 0)
- `countries`: split comma string if needed; uppercase; keep only codes that exist on `countries.code`; empty → `null`
- `config`: `PaymentGatewayRegistry::normalizeConfig($driver, config)`

Shared rules:

| Field | Rules |
| --- | --- |
| `code` | required, string, max 64, unique `payment_methods.code` (update ignores route `id`) |
| `name` | required, string, max 191 |
| `driver` | required, string, max 191; closure: registry `resolveDriverClass` **or** existing class subclass of `PaymentDriver`; else `Unknown payment driver.` |
| `is_active`, `allow_guest`, `send_invoice` | sometimes, boolean |
| `min_total`, `max_total` | nullable, numeric, min 0 |
| `countries` | nullable, array; `countries.*` string `Rule::in` country codes |
| `config` | nullable, array |
| `sort_order` | nullable, integer, min 0 |
| `channels` | nullable, array; `channels.*` exists `channels,id` |

Merged with `PaymentGatewayRegistry::validationRulesFor($driver)` as `config.{name}` from each field’s `rules` in `config/payments.php`.

### Driver field rules (from `config/payments.php`)

**`cod`**

| Config key | Rules |
| --- | --- |
| `title` | nullable, string, max 191 |
| `description` | nullable, string |
| `image` | nullable, string, max 255 |

**`raiaccept`**

| Config key | Rules |
| --- | --- |
| `title` | required, string, max 191 |
| `description` | nullable, string |
| `image` | nullable, string, max 255 |
| `merchant_id` | required, string, max 191 |
| `terminal_id` | required, string, max 191 |
| `gateway_url` | required, url, max 255 |
| `currency_numeric` | required, string, max 8 |
| `version` | required, string, max 16 |
| `locale` | required, string, max 8 |
| `capture_browser_return` | nullable, boolean |
| `return_url` | nullable, url, max 255 |
| `result_url` | nullable, url, max 255 |
| `success_url` | nullable, url, max 255 |
| `failure_url` | nullable, url, max 255 |
| `notify_url` | nullable, url, max 255 |
| `private_key_pem` | required, string |
| `bank_public_key_pem` | nullable, string |
| `debug` | nullable, boolean |

**`pcbbank`**

| Config key | Rules |
| --- | --- |
| `title` | required, string, max 191 |
| `description` | nullable, string |
| `image` | nullable, string, max 255 |
| `base_url` | required, url, max 255 |
| `order_type` | required, string, max 191 |
| `currency` | nullable, string, max 8 |
| `language` | nullable, string, max 8 |
| `return_url` | required, url, max 255 |
| `result_url` | nullable, url, max 255 |
| `client_cert_pem` | nullable, string |
| `client_key_pem` | nullable, string |
| `ca_cert_pem` | nullable, string |
| `client_cert_path` | nullable, string, max 255 |
| `client_key_path` | nullable, string, max 255 |
| `ca_cert_path` | nullable, string, max 255 |
| `verify_peer` | nullable, boolean |
| `debug` | nullable, boolean |

Admin boolean config fields use a hidden `0` plus checkbox `1`; `normalizeConfig` runs `FILTER_VALIDATE_BOOLEAN`.

Runtime `assertUsable` on PCB still requires cert+key even though PEM/path rules are `nullable`.

## API cart: set payment method

`App\Http\Requests\Api\V1\Cart\SetPaymentMethodRequest`

| Field | Rules |
| --- | --- |
| `payment_method_id` | required, integer, exists `payment_methods,id` |

Channel, active flag, excluded countries, and driver `assertUsable` are enforced in `CartService`, not this FormRequest.

## Related (checkout / sales specialist)

`PlaceOrderRequest` (`customer_note`, `accept_terms` required accepted, optional `email`) does not validate payment; payment is read from the cart.

`StoreOrderRequest` (sales specialist): `payment_method_id` required, exists active `payment_methods`; after-validation checks `PaymentMethod::active()->forChannel`. Redirect-driver exclusion is in the sales-specialist service, not this request.
