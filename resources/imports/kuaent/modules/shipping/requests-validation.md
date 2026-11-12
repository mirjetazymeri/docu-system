# Requests and validation

## Admin — shipping methods

`App\Http\Requests\Admin\ShippingMethod\StoreShippingMethodRequest`  
`UpdateShippingMethodRequest` extends store; only `code` uniqueness ignores current `{id}`.

`authorize()`: `true` (permission middleware is on the controller).

### `prepareForValidation`

- Driver from input, default `free`.
- Builds `config` **only** from `config("shipping.drivers.{$driver}.fields")` using `config_fields[$driver][$key]`, else field/default.
- Boolean fields: `'1'` / `1` / `true`.
- Number fields: float or driver default / 0.
- Other fields: string (or default).
- `countries`: comma-separated string or array → unique upper ISO2 codes that exist on `countries.code`. Empty → `null`.
- `is_active` / `allow_guest`: booleans.

Password-type BEKI `password` is stored as a string in `config` JSON like any other text field.

### Rules

| Field | Rules |
|---|---|
| `code` | required, string, max 64, unique `shipping_methods.code` (update: ignore route `id`) |
| `name` | required, string, max 191 |
| `driver` | required, string, max 191, `Rule::in` **keys of `config('shipping.drivers')`** |
| `is_active` / `allow_guest` | sometimes, boolean |
| `min_total` / `max_total` / `min_weight` / `max_weight` | nullable, numeric, min 0 |
| `countries` | nullable array; `*` string in existing country codes |
| `config` | nullable array (already shaped) |
| `sort_order` | nullable integer min 0 |
| `channels` | nullable array; `*` exists `channels,id` |

If `channels` is omitted on store/update, the controller syncs `[auth()->user()->current_channel_id]`.

There is no max-must-be-gte-min cross-field rule.

## Admin — shipping groups

No FormRequest. `ShippingGroupController::update` validates:

| Field | Rules |
|---|---|
| `name` | required, string, max 120 |
| `description` | nullable, string, max 1000 |
| `sort_order` | nullable integer 0–9999 |
| `is_fallback` | sometimes, boolean (`$request->boolean`) |
| `country_ids` | nullable array; `*` integer, exists `countries,id` |

`applySuggested` has no request body.

## Admin — country `shipping_group_id` (geo-countries)

`CountryController::validateCountry`: `shipping_group_id` nullable integer, `exists:shipping_groups,id`. Empty → `null`.

City: `beki_external_id` nullable string max 64 (not a shipping FormRequest).

## Storefront — select method

`App\Http\Requests\Api\V1\Cart\SetShippingMethodRequest`

| Field | Rules |
|---|---|
| `shipping_method_id` | required, integer, `exists:shipping_methods,id` |

Does **not** check active, channel, `frontend_visible`, or country. Those run in `CartService::setShippingMethod` (`findOrFail` on active + channel; country exclusion throws `RuntimeException`).

`GET cart/shipping/quotes` has no FormRequest.

## Sales specialist (consumer)

`StoreOrderRequest` / `PreviewCartRequest`: `shipping_method_id` exists (store: must be `is_active`). After validation, store may add “Selected shipping method is not available.” Fulfillment preview calls `shippingQuotes` rather than requiring an id.

Assignment store/update (orders): `shipping_method_id` required, exists `shipping_methods,id` — no channel/active check in that validator.
