# Tests

There is **no** dedicated shipping test suite. Searched `tests/` for `ShippingDriver`, `ShippingGroup`, `shippingQuotes`, `setShippingMethod`, `GroupRate`, `BekiPost`, `DHLExpress`, `FreeShipping`, `PostmanApi`, `cart/shipping` — no matches except incidental use below.

## What exists

### `tests/Feature/Bundle/BundleCartCheckoutTest.php`

Creates a `ShippingMethod` so place-order’s `cart.shipping.method_id` check passes:

- `code` `free`, `driver` **`free_shipping`** (not a key in `config/shipping.php`).
- Does not call `CartService::shippingQuotes` or any shipping driver.
- If quote/select ran with that driver string, `ShippingDriverManager` would throw `Unknown shipping driver` unless an FQCN class named `free_shipping` existed (it does not). The test only needs a row id on the cart.

### `tests/Unit/CheckoutAddressFieldsTest.php`

`test_shipping_rules_are_skipped_when_same_as_billing` — checkout **address** rules (`shipping.country`, etc.), not quotes.

### `tests/Unit/Pricing/*`

`shippingGross` / `shippingTaxable` / `shipping_total` are inputs to the **tax** calculator, not driver tests.

## Gaps (observed)

No tests for:

- `ShippingDriverManager` resolution (config key vs FQCN vs unknown)
- `GroupRateShippingDriver` (fallback group, per-item vs flat, ISO2 lookup)
- `FreeShippingDriver` / `BekiPostApiDriver` quote thresholds (`merchandiseTotal`)
- `DHLExpressDriver` weight formula and guest/weight gates
- `CartService::shippingQuotes` filters (`frontend_visible`, excluded countries, swallowed exceptions)
- `setShippingMethod` merge + `shipping_total`
- `ShippingGroupAssignmentService` XK/AL/MK/Europe/ROW
- BEKI `submitAssignment` HTTP (token, city map, AddOrder)
- Admin FormRequests (`driver` must be a registered key)
- Storefront `GET /cart/shipping/quotes` and `POST /cart/shipping/method`

`PostmanApiDriver` is unregistered; there are no tests that would have caught that.

## How to run (when tests exist)

```bash
php artisan test --filter=Shipping
```

Today that filter would not exercise this module’s drivers.
