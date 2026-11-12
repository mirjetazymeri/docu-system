# Controllers

Shipping-owned admin controllers live under `App\Http\Controllers\Admin`. Storefront quote/select actions live on cart-checkout’s `CartController`; they are listed here because they **are** this module’s HTTP API.

Country/city screens belong to **geo-countries**. Assignment/order shipment screens belong to **orders**. They are mentioned only where they touch shipping fields or drivers.

## `Admin\ShippingMethodController`

Permissions: `shipping_method_view` (index), `_create` (create/store), `_edit` (edit/update), `_delete` (delete).

| Method | Behavior |
|---|---|
| `index` | Blade list; AJAX DataTables of `ShippingMethod::forCurrentChannel()->with('channels')`. Search `q` on name/code. Columns: channels, guest, active, gates (min/max total & weight + excluded-country badges), driver, actions. |
| `create` / `store` | Form from `config('shipping.drivers')`. `StoreShippingMethodRequest`. Creates method, `channels()->sync` (`channels` or current channel). Redirect index. |
| `edit` / `update` | Loads channels; `UpdateShippingMethodRequest`; sync channels. Back with success. |
| `delete` | `findOrFail` + delete (pivot cascades). Redirect index. |

Views: `resources/views/admin/shipping_methods/{index,create,edit,_form,actions}.blade.php`. Driver `<select>` is **only** `config/shipping.php` keys. Config inputs are nested as `config_fields[{driver}][{key}]`.

## `Admin\ShippingGroupController`

Permissions: `country_view` (index, edit), `country_edit` (update, applySuggested).

| Method | Behavior |
|---|---|
| `index` | Groups with `countries` count, ordered `sort_order` then `number`. |
| `edit` | Group + all countries (`shippingGroup`) for checkboxes. |
| `update` | Inline validation (no FormRequest). Updates name/description/sort_order/`is_fallback`; if fallback, `markAsFallback()`. Sets `shipping_group_id` for checked `country_ids`; **unsets** countries previously in this group that were unchecked (including all, if the array is empty). |
| `applySuggested` | `ShippingGroupAssignmentService::applySuggestedAssignments()`. Flash counts G1–G4. |

Views: `resources/views/admin/shipping_groups/{index,edit}.blade.php`.

Groups have no create/delete controller actions; expected rows come from `ShippingGroupSeeder`.

## `Admin\CountryController` (geo-countries — shipping fields only)

Owned by geo-countries. Shipping-relevant:

- Index DataTables: filter `shipping_group_id`, column `shipping_group` (`G{n}` + name, or “Unassigned”).
- Index view: link to shipping groups; `$shippingGroups` for the filter.
- Create/edit: `<select name="shipping_group_id">`.
- `validateCountry`: `shipping_group_id` nullable, `exists:shipping_groups,id`.
- City store/update: `beki_external_id` → `City::syncExternalId(CityExternalId::PROVIDER_BEKI, …)` for BEKI AddOrder mapping.

## `Api\V1\CartController` (cart-checkout — shipping endpoints)

| Method | Request | Response |
|---|---|---|
| `shippingQuotes` | none | `{ "data": [ quote arrays ] }` from `CartService::shippingQuotes` (items.product loaded for DHL weights). |
| `setShippingMethod` | `SetShippingMethodRequest` | `CartResource` after `CartService::setShippingMethod`. |

Cart identity: `Authorization` (user) and/or `X-Cart-Token` / `cart_token`. Channel: `channel.api` (`X-Channel` / `?channel=` / default channel). See [api.md](api.md).

## Other controllers that call shipping (not owned here)

| Controller | Use |
|---|---|
| `Admin\LookupController::shippingMethods` | Select2: `ShippingMethod::forCurrentChannel()`, search name/code/driver. Permission `lookup_view`. Used by assignment forms. |
| `Admin\OrderController` | Passes active current-channel methods into shipment UI; `updateShipment` may set `shipping_method_id`. |
| `Admin\OrderShipmentAssignmentController` | Requires `shipping_method_id`; `OrderShipmentAssignmentService::syncToShipment` may call `submitAssignment`. |
| `SalesSpecialist\OrderController` | `fulfillmentOptions` → `CartService::shippingQuotes`; place order → `setShippingMethod`. |

There is **no** `Api\V1\Admin\ShippingCarrierConfigController` (listed as pending in PENDING_TASKS).
