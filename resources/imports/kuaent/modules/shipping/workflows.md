# Workflows

## 1. Quote a cart (storefront)

```
POST /cart/addresses     (country = ISO2 on shipping or billing)
        │
GET /cart/shipping/quotes
        │
        ├─ ShippingMethod::active()->forChannel(cart.channel_id)
        ├─ skip config.frontend_visible === false
        ├─ skip destination ISO2 in method.countries (exclusion)
        ├─ ShippingDriverManager::driverFor
        ├─ driver.quote(method, cart)   // assertUsable inside most drivers
        └─ on Throwable: skip method
        │
        ▼
JSON data[]  →  user picks id
        │
POST /cart/shipping/method { shipping_method_id }
        │
        ├─ active + channel or 404
        ├─ country exclusion → RuntimeException
        ├─ quote again (authoritative amount)
        ├─ merge into cart.shipping; cart.shipping_total = amount
        ├─ refreshCouponEffects (may zero shipping)
        └─ recalcTotals (tax may split shipping_tax)
        │
POST /checkout/place-order
        └─ requires cart.shipping.method_id; copies snapshot to OrderShipment
```

Destination for exclusion and group-rate: `shipping.country_code` or `shipping.country`, else billing. Checkout writes `country`.

If no address yet, group-rate still quotes using the **fallback group** (usually group 4). Exclusion does not apply when destination is empty.

## 2. Group-rate amount

```
ISO2 → countries.code → shipping_group_id → shipping_groups.number
        │ missing / unassigned
        ▼
ShippingGroup::fallback()  or  config.fallback_group (default 4)

amount = rate_{n}
if n in per_item_groups: amount *= sum(cart item qty)  (min qty 1)
```

Standard vs Express is **two method rows** (`group_rate` vs `group_rate_express`), not a flag on the group.

## 3. Assign countries to groups (admin)

**Manual:** `GET/PUT admin/shipping-groups/{id}` — check countries. Unchecked countries in that group get `shipping_group_id = null` (they do not auto-move to fallback).

**Per country:** geo-countries country form `shipping_group_id`.

**Bulk suggested:** `POST admin/shipping-groups/apply-suggested`

```
XK → 1
AL, MK → 2
other europeanIsoCodes() → 3
else → 4
```

Requires groups 1–4 to exist (`ShippingGroupSeeder`). Country import does **not** assign groups.

## 4. Configure a method (admin)

1. Create/edit under `admin/shipping-methods`.
2. Pick a **registered** driver; fill gates, excluded countries, channels, and driver `config_fields`.
3. `frontend_visible` false hides the method from quote lists (BEKI default).
4. Checkout uses the row immediately (no cache besides BEKI token/cities caches).

## 5. BEKI POST submit (after order)

Quote path never calls BEKI HTTP.

```
Admin creates/updates OrderShipmentAssignment (shipping_method_id = BEKI method)
        │
OrderShipmentAssignmentService::syncToShipment
        │  copies status/tracking onto OrderShipment; may transition order
        ▼
dispatchToProvider
        │  driverFor(assignment.shippingMethod)
        │  method_exists(submitAssignment)?
        ▼
BekiPostApiDriver::submitAssignment
        ├─ token (cached)
        ├─ IDCity from CityExternalId (beki) or GetCities
        ├─ POST AddOrder
        └─ save external_reference; shipment.meta.beki + provider_submission
```

Failure: assignment still saved; flash warning; `meta.provider_submission.status = failed`.

Drivers without `submitAssignment` (group-rate, free, DHL, unused Postman stub): `reason = driver_does_not_support_submission`.

## 6. Free-shipping coupon (cart-checkout)

After a quote is stored, a `free_shipping` coupon sets `shipping.orig_amount` and zeros `amount` / `shipping_total`. Removing the coupon restores `orig_amount`. This is coupon logic, not a shipping driver.

## Pending (not implemented here)

Queued send-to-carrier, POSTMAN carrier, status webhooks, admin carrier-config API: [`docs/shipping-integration-pending/PENDING_TASKS.md`](../../shipping-integration-pending/PENDING_TASKS.md).
