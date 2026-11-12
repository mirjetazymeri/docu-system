# Models

## `App\Models\ShippingMethod`

Table: `shipping_methods`.

| Attribute | Cast / notes |
|---|---|
| `code` | Unique string (e.g. `free`, `dhl_express`). Copied onto the quote as `methodCode`. |
| `name` | Display name (`methodName`). |
| `driver` | Config key (`group_rate`, `free`, …) or FQCN. Admin dropdown is config keys only. |
| `is_active` | Boolean. `scopeActive`. |
| `allow_guest` | Boolean. Enforced only by drivers that call it (`DHLExpressDriver`, `BekiPostApiDriver`). |
| `min_total` / `max_total` | `decimal:2`, nullable. Enforced by `BekiPostApiDriver::assertUsable` against `Cart::merchandiseTotal()`. Not used by group-rate / free / DHL quote paths. |
| `min_weight` / `max_weight` | `decimal:3`, nullable. Enforced by `DHLExpressDriver` against summed product weights. |
| `countries` | JSON array of ISO2 codes. **Quote-time exclusion list** (admin label: excluded countries). Empty/null = no country filter. |
| `config` | JSON object of driver fields (rates, URLs, credentials, `frontend_visible`, …). |
| `sort_order` | Unsigned int, default 10. |

Relations:

- `channels()` — `belongsToMany` `Channel` via `shipping_method_channel`.
- Inverse: `Channel::shippingMethods()`, `OrderShipment::shippingMethod()`, `OrderShipmentAssignment::shippingMethod()`.

Scopes:

- `active()` — `is_active = true`.
- `forCurrentChannel()` — methods attached to `auth()->user()->current_channel_id` (admin).
- `forChannel(?int $channelId)` — methods attached to that channel; if `$channelId` is falsy, `whereRaw('0 = 1')` (no rows).

There is no `shippingGroup()` on the method. Groups attach to **countries**, not methods.

## `App\Models\ShippingGroup`

Table: `shipping_groups`.

| Attribute | Notes |
|---|---|
| `number` | Unique tinyint. Quote config keys are `rate_{number}` / `eta_{number}`. Seeder uses 1–4. |
| `code` | Unique slug (`group_1` … `group_4`). |
| `name` / `description` | Admin labels. |
| `sort_order` | List order. |
| `is_fallback` | At most one should be true; `markAsFallback()` clears others. |

Relations:

- `countries()` — `hasMany` `Country` (geo-countries model).

Helpers:

- `fallback()` — first `is_fallback = true` by `number`, else the group with the highest `number`.
- `markAsFallback()` — exclusive fallback flag.

## Country (geo-countries owns the model)

`App\Models\Country` is **not** defined in this module. Shipping uses:

- `shipping_group_id` (nullable FK → `shipping_groups`, `nullOnDelete`)
- `shippingGroup()` `belongsTo` `ShippingGroup`
- `code` (ISO2) for destination lookup and method exclusion

Admin `CountryController` (geo-countries) validates `shipping_group_id` exists in `shipping_groups`, lists a shipping-group column/filter, and passes `$shippingGroups` to country create/edit forms.

Storefront `GET /api/v1/countries` does **not** expose `shipping_group_id`. Groups affect quotes only on the server.

## `City` / `CityExternalId` (geo-countries; used for BEKI)

`BekiPostApiDriver` resolves AddOrder `IDCity` in this order:

1. `config.force_city_id` if > 0
2. Local `City` match (by name + optional country) → `City::externalIdFor($provider)` where `$provider` defaults to `CityExternalId::PROVIDER_BEKI` (`beki`)
3. Live BEKI `GetCities` name match (aliases + normalization)

`CityExternalId` fillable: `city_id`, `provider`, `external_id`, `external_name`. Unique `(city_id, provider)`.

City CRUD and `beki_external_id` form fields live on `CountryController` (geo-countries). `CitySeeder` writes BEKI IDs for Kosovo cities.

## Cart / order models (consumers, not owned here)

`Cart` (cart-checkout):

- `shipping` JSON — address fields **merged** with quote fields after select: `method_id`, `method_code`, `method_name`, `amount`, `eta_text`, `service`, `meta` (and later `orig_amount` if a free-shipping coupon applies).
- `shipping_total` — quote amount (then tax/coupon may adjust displayed totals).
- `merchandiseTotal()` — `max(0, subtotal − discount_total)`; used by free/BEKI threshold quotes.
- `isGuest()` — `!user_id`.

Destination ISO2 is read from `shipping.country_code` **or** `shipping.country`, then the same keys on `billing`. Checkout address validation uses `country` (size 2), not `country_code`.

`OrderShipment` (orders): optional `shipping_method_id`; at place-order, `carrier_code` / `carrier_name` / `service` / `amount` / `eta_text` / `meta` are copied from `cart.shipping` **without** setting `shipping_method_id`.

`OrderShipmentAssignment` (orders): required `shipping_method_id`; BEKI submit reads this method’s `config`.
