# Architecture

Shipping is a **driver registry + quote service**, configured in admin, consumed by cart-checkout and (for BEKI) by order shipment assignment.

```
config/shipping.php                    # registered driver keys, labels, default config, admin field schema
        │
        ▼
shipping_methods (+ shipping_method_channel)
        │  driver = config key (or FQCN fallback)
        ▼
ShippingDriverManager::driverFor(method)
        │
        ▼
ShippingDriver::assertUsable() + quote()  →  ShippingQuote
        ▲
        │
CartService::shippingQuotes / setShippingMethod   (cart-checkout)
OrderShipmentAssignmentService::dispatchToProvider
        │  if method_exists(driver, 'submitAssignment')
        ▼
BekiPostApiDriver::submitAssignment()   # only driver that implements this
```

## Layers

### Config (`config/shipping.php`)

Each registered key has:

- `label` — admin dropdown
- `class` — FQCN implementing `ShippingDriver`
- `defaults` — copied into `shipping_methods.config` on seed / used as FormRequest fallbacks
- `fields` — admin form schema (`key`, `label`, `type` = `boolean` | `number` | `text` | `password`)

Admin create/update **only** persist config keys listed in `fields` for the selected driver. Extra keys typed in the form for another driver are dropped.

### Persistence

- **Method** rows: gates (`min_total` / `max_total` / `min_weight` / `max_weight`), `countries` JSON (used as an **exclusion** list at quote time), `config` JSON, `sort_order`, channel pivot.
- **Group** rows: numbered 1–4 in the seeder; countries point at a group via `countries.shipping_group_id` (column on the geo-countries `Country` model).

### Quote contract

`App\Services\Shipping\Contracts\ShippingDriver`:

1. `assertUsable(ShippingMethod, Cart): void` — throw `RuntimeException` to hide/fail the method.
2. `quote(ShippingMethod, Cart): ShippingQuote` — one best quote.

`ShippingQuote` is a readonly DTO: `methodCode`, `methodName`, `amount`, `etaText`, `serviceLevel`, `meta`.

### Resolution (`ShippingDriverManager`)

1. Look up `config("shipping.drivers.{$method->driver}.class")`.
2. If that class exists and resolves to `ShippingDriver`, use it.
3. Else, if `$method->driver` itself is an existing FQCN that implements `ShippingDriver`, use it.
4. Else `InvalidArgumentException: Unknown shipping driver`.

The FQCN fallback is how an unregistered class such as `PostmanApiDriver` *could* run, but no seeder or admin dropdown writes that value.

### Checkout consumption (this module’s API)

`CartService` (cart-checkout) is the only storefront caller of the manager for quotes:

1. Load **active** methods on the cart’s **channel**.
2. Skip if `config.frontend_visible` is false (missing key counts as visible).
3. Skip if destination ISO2 is in `method.countries` (exclusion list).
4. `driverFor` + `quote`. Failed quotes are **swallowed** (`catch (Throwable)`); that method is omitted.
5. Return arrays including `method_id` / `id` plus quote fields.

`setShippingMethod` does **not** check `frontend_visible`. A hidden method (e.g. BEKI with default `frontend_visible: false`) can still be applied if the client knows `shipping_method_id`, as long as it is active, on-channel, and not country-excluded.

Place-order (cart-checkout) requires `cart.shipping.method_id` and that the method is still active on the channel. It does not re-quote.

### Group geography

`GroupRateShippingDriver` maps cart destination → `Country.code` → `Country.shippingGroup` → group `number` → `config['rate_{n}']` / `eta_{n}`. Missing country or unassigned country uses `ShippingGroup::fallback()` (the `is_fallback` row, else the highest `number`).

Suggested assignment (`ShippingGroupAssignmentService`) writes `shipping_group_id` for every country. It is an admin POST, not automatic on country import.

### Assignment / carrier submit (orders → this module)

`OrderShipmentAssignmentService::syncToShipment` (orders) calls `dispatchToProvider`:

- Resolve driver via `ShippingDriverManager`.
- If the driver has `submitAssignment`, call it (today: **BEKI only**).
- On failure, write `order_shipments.meta.provider_submission` and show an admin warning; the assignment row is still saved.

`OrderService::place` creates `OrderShipment` from cart shipping **labels and amount**. It does **not** set `order_shipments.shipping_method_id` (that column is filled later in admin shipment edit or via assignment).

## Admin UI

- Sidebar under shipping: methods CRUD + shipping groups (groups gated by `country_view`).
- Countries index (geo-countries) filters and edits `shipping_group_id`; city forms edit BEKI external IDs used at AddOrder time.

## What is not in this architecture

- No queued `SendOrderToCarrierJob`.
- No webhook routes `POST /api/v1/webhooks/shipping/{carrier}/status`.
- No `ShippingCarrierConfigController`.
- No POSTMAN carrier class (only the unused quote stub).

See [`docs/shipping-integration-pending/PENDING_TASKS.md`](../../shipping-integration-pending/PENDING_TASKS.md).
