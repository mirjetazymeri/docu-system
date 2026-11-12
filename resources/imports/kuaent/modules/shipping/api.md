# Storefront shipping API

Checkout (and sales-specialist fulfillment preview) obtain prices through these endpoints. This is **this module’s HTTP surface**, not a full checkout guide. Cart create, addresses, payment, and place-order live in cart-checkout.

Base: `/api/v1`  
Middleware: `channel.api`, `locale.api`

## Headers

| Header / query | Role |
|---|---|
| `Accept: application/json` | |
| `Content-Type: application/json` | POST |
| `Authorization: Bearer …` | Logged-in cart |
| `X-Cart-Token` or `?cart_token=` | Guest cart token (`CartController::extractToken`) |
| `X-Channel` or `?channel=` | Channel code; else user current channel; else channel `default` |

Quotes are filtered to methods pivoted to the resolved channel.

Typical sequence: set addresses (`POST /cart/addresses`, field `country` ISO2) → **GET quotes** → **POST method** → place-order (requires `cart.shipping.method_id`).

## `GET /api/v1/cart/shipping/quotes`

Returns methods that are active, on-channel, `config.frontend_visible` not false, destination not in the method’s excluded `countries`, and whose `quote()` did not throw.

**Response**

```json
{
  "data": [
    {
      "methodCode": "free",
      "methodName": "Standard Delivery",
      "amount": 2.5,
      "etaText": "3-5 business days",
      "serviceLevel": "Standard",
      "meta": {
        "rule": "threshold_pricing",
        "free_over_total": 50,
        "below_threshold_fee": 2.5,
        "cart_total": 40
      },
      "method_id": 1,
      "id": 1
    }
  ]
}
```

`id` and `method_id` are the same `shipping_methods.id` (use either as `shipping_method_id` on POST).

Group-rate `meta` (when that driver is used) includes `rule: "group_rate"`, `shipping_group`, `shipping_group_name`, `pricing`, `unit_rate`, `qty`. DHL includes `weight_kg`. Failed driver quotes are omitted (empty `data` if none succeed).

BEKI with default `frontend_visible: false` does **not** appear here.

## `POST /api/v1/cart/shipping/method`

**Body**

```json
{
  "shipping_method_id": 1
}
```

Validation: required integer, exists on `shipping_methods`. Service layer then requires **active + current channel**. Excluded destination country → `RuntimeException` (`Shipping method is not available for destination country.`). `frontend_visible` is **not** checked.

**Response:** `CartResource` — `shipping` JSON now includes (merged with address fields):

| Key | Source |
|---|---|
| `method_id` | Method id |
| `method_code` | Quote `methodCode` |
| `method_name` | Quote `methodName` |
| `amount` | Quote amount |
| `eta_text` | Quote ETA |
| `service` | Quote `serviceLevel` |
| `meta` | Quote meta |

`totals.shipping_total` follows the quote (then tax/coupon). A `free_shipping` coupon may set `shipping.orig_amount` and zero `amount` / `shipping_total`.

## Place-order coupling (cart-checkout)

`OrderService::place` requires `cart.shipping.method_id` > 0 and that the method is still active on the cart channel. It does not call `quote()` again. The shipment row stores carrier labels and amount from `cart.shipping`; see [dependencies.md](dependencies.md).

## Related storefront routes (not shipping-owned)

- `GET /api/v1/countries` — ISO2 list; **no** shipping group.
- `GET /api/v1/countries/{code}/cities` — city names for the address UI; BEKI IDs are admin-only.

There is no public “list all shipping methods” or “track shipment” API in this module.
