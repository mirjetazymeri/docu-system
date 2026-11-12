# Services

All classes under `App\Services\Shipping` unless noted.

## Contracts

### `Contracts\ShippingDriver`

```php
public function assertUsable(ShippingMethod $method, Cart $cart): void;
public function quote(ShippingMethod $method, Cart $cart): ShippingQuote;
```

Throw `\RuntimeException` from `assertUsable` (or from `quote`) when the method cannot apply. Checkout quote listing catches **any** `Throwable` and omits that method.

Optional (not on the interface): `submitAssignment(OrderShipmentAssignment $assignment): array` — only `BekiPostApiDriver` implements it. `OrderShipmentAssignmentService` uses `method_exists`.

### `Contracts\ShippingQuote`

Readonly DTO / `JsonSerializable`: `methodCode`, `methodName`, `amount` (float), `etaText`, `serviceLevel`, `meta` (array). `toArray()` uses those camelCase keys.

## `ShippingDriverManager`

`driverFor(ShippingMethod $method): ShippingDriver`

1. `config("shipping.drivers.{$method->driver}.class")` if it is a string, `class_exists`, and `app()` returns `ShippingDriver`.
2. Else `class_exists($method->driver)` and same instanceof check (FQCN stored on the row).
3. Else `InvalidArgumentException("Unknown shipping driver: {$driver}")`.

No service-provider map beyond Laravel container `app($class)`.

## `ShippingGroupAssignmentService`

`applySuggestedAssignments(): array{assigned:int, byGroup: array<int,int>}`

Requires at least four `ShippingGroup` rows keyed by `number` 1–4; otherwise `RuntimeException`.

For every `Country` (chunked by id):

| ISO2 | Group number |
|---|---|
| `XK` | 1 |
| `AL`, `MK` | 2 |
| Code in `CountriesNowImportService::europeanIsoCodes()` | 3 |
| else | 4 |

The Europe list **includes** XK/AL/MK/ME; `match` order assigns XK and AL/MK first, so they never land in group 3. ME and other European codes go to 3. Updates `shipping_group_id` only when it changed. Returns counts per group (every country is counted, not only updates).

Europe ISO2 set is owned by geo-countries (`CountriesNowImportService::europeanIsoCodes()`).

---

## Registered drivers

Only these keys appear in `config/shipping.php`. Both `group_rate` and `group_rate_express` use the same class with different **defaults**.

### `Drivers\GroupRateShippingDriver` (`group_rate`, `group_rate_express`)

- `assertUsable`: no-op (missing address falls back to fallback group in `quote`).
- Destination: `cart.shipping.country_code` / `country`, else billing same keys, uppercased.
- Group: `Country` by `code` → `shippingGroup`, else `ShippingGroup::fallback()`.
- Group number: resolved group’s `number`, else `config.fallback_group` (default 4).
- Rate: `config["rate_{n}"]` else `rate_4` else 0.
- ETA: `eta_{n}` else `eta_4` else `eta_text`.
- `service_level` from config (Standard vs Express defaults).
- `per_item_groups`: comma/whitespace list of group numbers. If the destination group is in that list, `amount = rate * max(1, sum of cart item qtys)`; else flat `rate`. Meta includes `rule=group_rate`, `shipping_group`, `shipping_group_name`, `pricing` (`per_item`|`flat`), `unit_rate`, `qty`.

Config defaults (Standard vs Express) are in `config/shipping.php` (`rate_*`, `eta_*`, Express `per_item_groups` = `3,4`, Express rates 100 on groups 3–4).

### `Drivers\FreeShippingDriver` (`free`)

- `assertUsable`: no-op (comment: availability skipped; threshold only).
- Amount: `0` if `Cart::merchandiseTotal() >= free_over_total` (default 50), else `below_threshold_fee` (default 2.5).
- `serviceLevel`: always `Standard`.
- Meta: `rule=threshold_pricing`, threshold, fee, `cart_total`.

Does **not** honor `allow_guest` / min-max total / weight / country (country exclusion is applied in `CartService` before quote).

### `Drivers\DHLExpressDriver` (`dhl_express`)

Not a live DHL HTTP API. Local formula.

- `assertUsable`: if `!allow_guest` and guest cart → exception. Weight vs `min_weight` / `max_weight` on the **method** (not config).
- Weight: sum `(product.weight ?? default_item_weight) * qty` over `$cart->items` (`items.product` should be loaded).
- Amount: `max(min_rate, base_rate + per_kg * max(0, weight_kg − 1))` (first kg included in base).
- `serviceLevel`: `Express`. Meta: `weight_kg`.

### `Drivers\BekiPostApiDriver` (`beki_post_api_standard`)

**Quote** does not call BEKI. Same threshold pattern as free shipping (`merchandiseTotal` vs `free_over_total` / `below_threshold_fee`). Meta includes `provider`, `service_code`, `api_base_url`, `timeout_seconds`, `integration=beki`. Default `frontend_visible` is **false**.

**`submitAssignment`** (orders assignment sync):

1. Skip if `submit_on_assignment` is false; if `external_reference` already set; if assignment canceled.
2. Require `id_user`.
3. OAuth-style token: GET form-urlencoded, then GET query, then POST form to `token_url` (`username`, `password`, `grant_type`). Cached 30 minutes as `shipping.beki.token.{md5}`.
4. Resolve city ID (mapped `CityExternalId`, then GetCities). Cities cached 12 hours: `shipping.beki.cities.{md5}`.
5. POST form AddOrder (`api_base_url` + `add_order_path`) with `IDUser`, `OrderNumber`, `FullName`, `Address`, `IDCity`, `Phone`, `Description`, `Amount` (order `grand_total`), `Comment`, `CanOpen`, `IDDeliveryType`, `WCSOrder`.
6. Persist `external_reference` / `tracking_number` on the assignment; write `meta.beki` and `meta.provider_submission` on the shipment.

Config keys (names only): `token_url`, `api_base_url`, `cities_path`, `add_order_path`, `city_provider`, `username`, `password`, `grant_type`, `id_user`, `city_scope_id`, `delivery_type_id`, `can_open`, `submit_on_assignment`, `service_code`, `timeout_seconds`, plus quote fields `free_over_total`, `below_threshold_fee`, `eta_text`, `frontend_visible`, `provider_name`. Optional undocumented keys used in code: `force_city_id`, `city_aliases`.

No env vars. Credentials are method `config` values.

Kosovo city aliases are hardcoded on the driver (Pristina/Peja/…).

---

## Unregistered driver on disk

### `Drivers\PostmanApiDriver`

- File: `app/Services/Shipping/Drivers/PostmanApiDriver.php`.
- Implements `ShippingDriver`.
- **Not** in `config/shipping.php`. **No** references elsewhere in the repo.
- Quote: threshold vs `$cart->grand_total` (not `merchandiseTotal()`), `serviceLevel` `API`, meta `provider` / `service_code` / `api_base_url` / `timeout_seconds`.
- Empty `assertUsable`. **No** `submitAssignment`.

Observed status: **unused / unregistered**. Admin cannot pick it from the driver dropdown. `ShippingDriverManager` would only load it if `shipping_methods.driver` were the FQCN `App\Services\Shipping\Drivers\PostmanApiDriver`. PENDING_TASKS still describes a future POSTMAN **carrier** class that does not exist.

---

## Callers outside `App\Services\Shipping`

| Class | Role |
|---|---|
| `App\Services\Checkout\CartService` | `shippingQuotes`, `setShippingMethod`, country exclusion, writes `cart.shipping` + `shipping_total` |
| `App\Services\OrderShipmentAssignmentService` | After sync, `submitAssignment` if present |
| `App\Services\Checkout\OrderService` | Requires `cart.shipping.method_id`; copies shipping snapshot onto `OrderShipment` (no `shipping_method_id`) |
| `App\Services\Checkout\CouponService` | `free_shipping` coupon zeros `shipping.amount` / `shipping_total`, stashes `orig_amount` |
| `App\Services\SalesSpecialist\SalesSpecialistOrderService` | Same `CartService` quote/select |

`CartService::setShippingMethod` merges quote onto existing `cart.shipping` (addresses preserved), sets `shipping_total`, then `refreshCouponEffects` + `recalcTotals` (tax uses `cart.shipping.amount` when `tax.shipping.taxable` is on — tax module, not shipping).
