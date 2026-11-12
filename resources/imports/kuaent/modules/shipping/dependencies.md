# Dependencies

Verified against this codebase. Shipping is not a Laravel package; “depends on” means it reads/writes those modules’ models or is invoked by them.

## geo-countries — **depends**

| Use | Where |
|---|---|
| `Country` + `shipping_group_id` / `shippingGroup()` | `GroupRateShippingDriver`, `ShippingGroupAssignmentService`, `ShippingGroupController`, method FormRequests (country codes), method index badges |
| `CountriesNowImportService::europeanIsoCodes()` | Suggested group assignment |
| `City` + `CityExternalId` (`PROVIDER_BEKI`) | `BekiPostApiDriver` city mapping |
| Country admin `shipping_group_id` and `beki_external_id` | `CountryController` (owned by geo-countries) |

Country catalog, import, and city CRUD stay in geo-countries. Shipping only **uses** the group FK and BEKI external IDs.

Storefront country/city JSON does not include shipping groups.

## cart-checkout — **consumed by** (and shipping quotes live on cart HTTP)

| Use | Where |
|---|---|
| `Cart`, `Cart::merchandiseTotal()`, `isGuest()`, `shipping` / `billing` JSON, `shipping_total` | All drivers + `CartService` |
| `CartService::shippingQuotes` / `setShippingMethod` / country exclusion | Checkout and sales-specialist |
| `CartController` GET/POST shipping | Storefront API |
| `SetShippingMethodRequest` | POST body |
| `OrderService::place` | Requires `shipping.method_id`; copies quote snapshot to shipment |
| `CouponService` `free_shipping` | Zeros quoted amount |
| `TaxService` | Uses `cart.shipping.amount` when shipping is taxable (tax module setting `tax.shipping.taxable`) |

Shipping does not create carts. Checkout must set addresses before group-rate/exclusion reflect the destination. Place-order is cart-checkout; it does not re-quote.

Product `weight` (catalog) is read by `DHLExpressDriver` via `$item->product`.

## orders — **consumed by**

| Use | Where |
|---|---|
| `OrderShipment` | Place-order snapshot; BEKI writes `meta` / tracking; optional `shipping_method_id` from admin |
| `OrderShipmentAssignment` | `shipping_method_id`; `BekiPostApiDriver::submitAssignment` |
| `OrderShipmentAssignmentService` | `ShippingDriverManager` + optional `submitAssignment` |
| `OrderShipmentAssignmentController` | Admin assignment CRUD |
| `OrderController::updateShipment` | May set `shipping_method_id` |
| `LookupController::shippingMethods` | Select2 for assignments |
| `Order.shipping_total` | Copied from cart/tax quote at place |

Shipping does not own order status machines. PENDING_TASKS’ webhook/job files are **not** implemented; do not treat them as current dependencies.

## Other internals

| Piece | Relation |
|---|---|
| `Channel` / `shipping_method_channel` | Method visibility per storefront channel |
| `config/shipping.php` | Driver registry |
| Laravel HTTP + Cache | BEKI token/cities |
| Permissions `shipping_method_*`, `country_*`, `lookup_view`, `assignment_*` | Admin gates |

## Environment

No shipping-specific env **names** are referenced. BEKI `username` / `password` / URLs are `shipping_methods.config` keys.

## Direction summary

```
geo-countries  ──Country.shipping_group_id, CityExternalId──►  shipping
shipping       ──ShippingDriverManager, ShippingQuote──────►  cart-checkout
shipping       ──submitAssignment (BEKI only)──────────────►  orders (assignment)
cart-checkout  ──cart.shipping snapshot────────────────────►  orders (place-order)
```
