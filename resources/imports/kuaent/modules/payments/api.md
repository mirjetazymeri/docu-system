# Payments API

Base: `/api/v1`. Middleware: `channel.api`, `locale.api`. Channel: query `channel`, header `X-Channel`, authenticated current channel, else code `default`. Guest cart: header `X-Cart-Token` or query `cart_token`. Optional Sanctum `Authorization: Bearer`.

## List methods

`GET /payment/methods`

May create an active cart if none exists (same token/user/channel rules as the payment-method controller).

**200**

```json
{
  "data": [
    {
      "id": 1,
      "code": "cod",
      "name": "Cash on Delivery",
      "driver": "cod",
      "sort_order": 0,
      "min_total": null,
      "max_total": null,
      "allow_guest": true,
      "config": {
        "title": "Cash on Delivery",
        "description": null,
        "image": null
      }
    }
  ]
}
```

Public `config` keys only:

- `cod`: `title`, `description`, `image`
- `raiaccept`: `title`, `description`, `image`, `locale`
- `pcbbank`: `title`, `description`, `image`, `language`

**400** if channel not resolved.

## Attach method to cart

`POST /cart/payment/method`

Body: `{ "payment_method_id": 1 }` (exists on `payment_methods`).

Runs `assertUsable` + `initiate`. Recalculates cart totals first.

**200** `CartResource` including `payment` (`id`, `cart_id`, `payment_method_id`, `amount`, `status`, `reference`, `payload`, timestamps).

Failures surface as `RuntimeException` from `CartService` (inactive/wrong channel `findOrFail`, excluded country, driver assert/initiate).

## Place order (payment branch)

`POST /checkout/place-order`  
Body includes `accept_terms` (must be accepted). Optional `customer_note`, `email`. Optional header `Idempotency-Key` (COD path only).

**202** when the selected driver implements `RedirectPaymentDriver`:

RaiAccept `payload` is the bank form (`Version`, `MerchantID`, `TerminalID`, `PurchaseTime`, `OrderID`, `Currency`, `TotalAmount`, `PurchaseDesc`, `locale`, `Signature`, optional URL fields). `gateway_url` is the hosted pay URL. Storefront POSTs that form.

PCB `payload` is `{ "id": "<txpg id>", "password": "…" }`. `gateway_url` is the HPP URL already including `id` and `password` query params.

```json
{
  "data": {
    "status": "payment_redirect_required",
    "gateway": "raiaccept",
    "gateway_url": "https://…",
    "gateway_order_id": "ORD…",
    "payload": {}
  }
}
```

**201** COD (or any non-redirect driver): `OrderResource`.  
**200** if cart already `ordered` with `order_id`.  
**422** redirect-session errors or `OrderService` failures.

## Browser return

`GET|POST /payments/{gateway}/return`

Bank redirects the shopper here when `SUCCESS_URL`/`FAILURE_URL` or PCB `hppRedirectUrl` point at this API.

**RaiAccept query/body:** at least `OrderID`; typically `TranCode`, `Signature`, plus echoed purchase fields.

**PCB:** `ID` or `id`; optional `STATUS`/`status`.

Success path: HTTP redirect to storefront `result_url` with `gateway` and `gateway_order_id`.  
Unknown gateway: JSON 404.

## Capture browser result

`POST /payments/{gateway}/capture-browser-result`

Same handler `source=return` as above, but JSON only (for storefronts that landed on SUCCESS_URL with `OrderID`/`TranCode` in the query and POST them here).

RaiAccept JSON 200 example:

```json
{
  "success": true,
  "gateway": "raiaccept",
  "gateway_order_id": "ORD…",
  "status": "authorized",
  "queued": true
}
```

`queued` is always `true` in the handler body even though the job runs via `dispatchSync`.

PCB JSON uses the same shape with `status` from `PcbBankStatus::toInternal`.

## Server notify

`GET|POST /payments/{gateway}/notify`

**RaiAccept:** plain text, `Response.action=approve`, optional `Response.forwardUrl`, plus echoed scalar fields. HTTP status from the controller is 200 whenever `notify_text` is present.

**PCB:** JSON acknowledgement (no `notify_text`).

**Unknown gateway:** `200` `text/plain`:

```
Response.action=decline
Response.reason=unsupported_gateway
```

## Poll status

`GET /payments/{gateway}/status?gateway_order_id=`

**422** missing `gateway_order_id`.  
**404** `{ data: { status: "not_found", gateway, gateway_order_id } }` if no `cart_payments.reference` match or driver mismatch.

**200**

```json
{
  "data": {
    "status": "authorized",
    "gateway": "raiaccept",
    "gateway_order_id": "ORD…",
    "cart_id": 12,
    "order_id": 45
  }
}
```

`status` is `cart_payments.status` (`pending`, `authorized`, `failed`, `cancelled`, …). `order_id` is `carts.order_id` (null until finalize places). This endpoint does not call the bank.
