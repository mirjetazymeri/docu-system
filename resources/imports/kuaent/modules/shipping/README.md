# Shipping

Admin-configured shipping methods and country groups, plus quote drivers used at checkout. This module prices a cart; it does not own cart persistence, order placement, or the Country catalog.

Carrier **order submission** exists only for BEKI POST (`BekiPostApiDriver::submitAssignment`). Live DHL/POSTMAN rating APIs are not implemented. Outstanding carrier/webhook work is listed in [`docs/shipping-integration-pending/PENDING_TASKS.md`](../../shipping-integration-pending/PENDING_TASKS.md) — that file is the pending-task source of truth and is not replaced by this folder.

## What this module does

- CRUD for **shipping methods** (driver, gates, excluded countries, per-driver `config`, channel assignment).
- CRUD-lite for **shipping groups** (name, fallback flag, country membership) and a one-shot **suggested geography** assignment.
- Resolves a **quote** (`amount`, ETA, service level, meta) for a cart + method via a driver from `config/shipping.php`.
- Exposes storefront quote/select HTTP endpoints that checkout calls (see [api.md](api.md)).
- After an admin **shipment assignment**, optionally submits the order to BEKI POST.

## What it does not own

| Concern | Owner |
|---|---|
| `Country`, `City`, `CityExternalId` models and country/city admin CRUD | **geo-countries** (`CountryController` still exposes `shipping_group_id` and BEKI city IDs for this module) |
| Cart create/items/addresses, totals, coupons, place-order | **cart-checkout** |
| `Order`, `OrderShipment`, `OrderShipmentAssignment` persistence and admin shipment UI | **orders** |

Checkout **calls** this module’s quote API (`CartService::shippingQuotes` / `setShippingMethod`). Full checkout flow is documented elsewhere.

## Registered drivers

Keys in `config/shipping.php` `drivers` (the only drivers the admin form and FormRequests accept):

| Config key | Class | Role |
|---|---|---|
| `group_rate` | `GroupRateShippingDriver` | Flat (or per-item) rate by country’s shipping group — Standard defaults |
| `group_rate_express` | `GroupRateShippingDriver` | Same class — Express defaults (per-item on groups 3 and 4) |
| `free` | `FreeShippingDriver` | Threshold: free at/above merchandise total, else a fee |
| `dhl_express` | `DHLExpressDriver` | Local formula: base + per-kg (not a live DHL API) |
| `beki_post_api_standard` | `BekiPostApiDriver` | Threshold quote (usually hidden); AddOrder on assignment |

**Not registered:** `App\Services\Shipping\Drivers\PostmanApiDriver` exists on disk and implements `ShippingDriver`, but it is **not** listed in `config/shipping.php` and has **no** callers. Treat it as unused/unregistered unless a row stores its FQCN in `shipping_methods.driver` (manager fallback). See [services.md](services.md).

Do not invent additional carriers. PENDING_TASKS still describes POSTMAN/BEKI **carrier jobs and webhooks** that are not in this codebase yet.

## Docs in this folder

| File | Contents |
|---|---|
| [architecture.md](architecture.md) | Layers, quote pipeline, assignment submit |
| [models.md](models.md) | `ShippingMethod`, `ShippingGroup`, Country/City fields this module uses |
| [database.md](database.md) | Tables, FKs, seeders |
| [controllers.md](controllers.md) | Admin + cart API controllers that touch shipping |
| [services.md](services.md) | Manager, group assignment, each driver |
| [requests-validation.md](requests-validation.md) | FormRequests and inline rules |
| [routes.md](routes.md) | Admin web + storefront API routes |
| [api.md](api.md) | Storefront `GET/POST` shipping endpoints |
| [workflows.md](workflows.md) | Quote flow, group assignment, BEKI submit |
| [dependencies.md](dependencies.md) | geo-countries, cart-checkout, orders |
| [tests.md](tests.md) | What exists (almost nothing dedicated) |

## Environment

No `SHIPPING_*` (or other shipping-specific) environment variable names are read by this module. BEKI credentials and URLs live on the method row as `shipping_methods.config` keys (`username`, `password`, `token_url`, `api_base_url`, …). Store secrets in the database config JSON, not in git.

## Permissions

| Permission | Use |
|---|---|
| `shipping_method_view` / `_create` / `_edit` / `_delete` | Shipping method admin |
| `country_view` / `country_edit` | Shipping **groups** admin (`ShippingGroupController`) |
| `lookup_view` | Admin Select2 of shipping methods (assignments UI) |
| `assignment_*` | Shipment assignment (orders module) that may call BEKI submit |
