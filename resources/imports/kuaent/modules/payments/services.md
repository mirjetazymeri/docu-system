# Payments services

## `PaymentGatewayRegistry`

`App\Services\Payment\PaymentGatewayRegistry`

| Method | Role |
| --- | --- |
| `all()` | `config('payments.drivers')` |
| `get($driver)` | One alias definition |
| `knownDriverAliases()` | Keys: `cod`, `raiaccept`, `pcbbank` |
| `availableDrivers()` | `{ alias, label, driver_class }[]` for admin |
| `resolveDriverClass` | `driver_class` or FQCN implementing `PaymentDriver` |
| `resolveCallbackHandlerClass` | `callback_handler` implementing `PaymentCallbackHandler` |
| `fieldsFor` / `validationRulesFor` / `defaultsFor` | Admin form |
| `normalizeConfig` | Merge defaults; coerce `boolean` fields |
| `publicConfigFor` | Fields with `public => true` |

## `PaymentDriverManager`

`App\Services\Payment\PaymentDriverManager`

`driverFor(PaymentMethod)`: `app($class)` must implement `PaymentDriver`, else `InvalidArgumentException("Unknown payment driver: …")`.

**Called from:** `CartService::setPaymentMethod`, `CheckoutController::placeOrder`, `SalesSpecialistOrderService::previewFulfillment`, `SalesSpecialist\OrderController::searchPaymentMethods`.

## Contracts

### `PaymentDriver`

`assertUsable(PaymentMethod, Cart): void` — throws `RuntimeException`.

`initiate(Cart, PaymentMethod): CartPayment`

`capture(CartPayment): CartPayment` — unused by callers; all drivers return the same row (COD comment: capture is offline).

`cancel(CartPayment): CartPayment` — unused by callers; sets `status` `cancelled`.

### `RedirectPaymentDriver`

`gatewayRedirect(Cart): array` — `status` `payment_redirect_required`, `gateway`, `gateway_url`, `gateway_order_id`, `payload`.

### `PaymentCallbackHandler`

`process(Request, string $source): array` — `status`, `body`, optional `redirect`, `notify_text`, `notify_content_type`. `$source` is `return` or `notify`.

## `PaymentReceipt`

`App\Services\Payment\PaymentReceipt`

Normalizes `order_payments.data.pcbbank` or `.raiaccept` for invoices and payment-confirmation mail.

| Field | PCB (`data.pcbbank`) | RaiAccept (`data.raiaccept`) |
| --- | --- | --- |
| `orderId` | `order.id` or `gateway_order_id` | `gateway_order_id` or `raw.OrderID` |
| `approvalCode` | `order.trans[0].approvalCode` | `approval_code` or `raw.ApprovalCode` |
| `cardBrand` | `order.srcToken.card.brand` | inferred from `raw.ProxyPan` first digit (4 Visa, 2/5 Mastercard, 3 American Express, 6 Discover) |
| `cardLast4` | last 4 digits of `srcToken.displayName` | last 4 of `ProxyPan` |
| `transactionAt` | `trans[0].regTime` or `order.createTime` | `raw.PurchaseTime` as `ymdHis` (12 digits) |

**Called from:** `MailService::sendPaymentConfirmation`, `InvoiceService::generate`.

---

## Driver: Cash on Delivery

**Class:** `App\Services\Payment\Drivers\CashOnDeliveryDriver`  
**Alias:** `cod`  
**Implements:** `PaymentDriver` only (not redirect).

**Config keys (names):** `title`, `description`, `image`.

**Public config:** `title`, `description`, `image`.

**Called from:** `CartService::setPaymentMethod` → `initiate`. Place-order uses `OrderService::place` (no `gatewayRedirect`).

**Webhooks:** none (`callback_handler` omitted). `/payments/cod/…` is unsupported.

**Failure handling:** `assertUsable` throws `RuntimeException` for guest (when `setting('system.checkout')` is false and `allow_guest` is false) and min/max total. `initiate` writes `status` `pending`, `reference` `''`, `payload.note`.

**Timeouts / retries:** none in this class.

---

## Driver: RaiAccept

**Class:** `App\Services\Payment\Drivers\RaiAccept\RaiAcceptDriver`  
**Alias / `GATEWAY`:** `raiaccept`  
**Implements:** `PaymentDriver`, `RedirectPaymentDriver`.

**Helpers**

- `RaiAcceptSignature` — request sign string `MerchantID;TerminalID;PurchaseTime;OrderID;Currency;TotalAmount;`; `openssl_sign` SHA-512, Base64. `verifyFlexible` tries two response strings (last field `Rrn` or `XID`).
- `RaiAcceptResponse::buildResponseString` — same Rrn variant as the first verify string. **Not referenced** by the driver or callback handler.

**Config keys (names):** `title`, `description`, `image`, `merchant_id`, `terminal_id`, `gateway_url`, `currency_numeric`, `version`, `locale`, `capture_browser_return`, `return_url`, `result_url`, `success_url`, `failure_url`, `notify_url`, `private_key_pem`, `bank_public_key_pem`, `debug`.

**Env names used as field defaults in `config/payments.php`:** `RAIACCEPT_RETURN_URL`, `RAIACCEPT_RESULT_URL`, `RAIACCEPT_NOTIFY_URL`, `APP_URL`.

**Required at `assertUsable`:** `merchant_id`, `terminal_id`, `gateway_url`, `currency_numeric`, `private_key_pem`.

**Public config:** `title`, `description`, `image`, `locale`.

**Called from:** `CartService::setPaymentMethod` (`initiate`); `CheckoutController::placeOrder` (`gatewayRedirect`). `initiate` reuses a pending session when method, `pending` status, gateway, `reference`/`gateway_url`/`request.OrderID`, and amount match (avoids a second OrderID if checkout saves payment then place-order saves again).

**Redirect URLs:** if `capture_browser_return` is true (default), `SUCCESS_URL` / `FAILURE_URL` are `return_url` (or `{APP_URL}/api/v1/payments/raiaccept/return`) with `outcome=success|failure`. If false, uses `success_url` / `failure_url` as stored.

**Amount:** minor units: `(int) round(grand_total * 100)` as string `TotalAmount`. `PurchaseTime` `ymdHis`. `OrderID` `ORD{cartId}{YmdHis}`.

**Webhooks / callbacks:** `RaiAcceptCallbackHandler` on return, notify, and capture-browser-result (all `process` with `return` or `notify`). Looks up `CartPayment` by `OrderID`. Verifies `Signature` with `bank_public_key_pem` (`verified` may be false if key missing/invalid; processing still continues). Success TranCode: handler `isSuccessTranCode` strips non-digits and left-pads to 3 chars, success if `000`. Terminal cart statuses `authorized|failed|cancelled` skip a second persist/job. Otherwise persist callback + `FinalizeRaiAcceptOrderJob::dispatchSync`.

Notify response (UPC/Raiffeisen style): echo scalar request fields, `Response.action=approve`, optional `Response.forwardUrl`. `PaymentCallbackController` still returns HTTP 200 when `notify_text` is set (including handler 404/422 bodies).

Browser return: redirect to `result_url` (fallback hardcoded in handler if empty) with `gateway=raiaccept&gateway_order_id=`.

**Failure handling:** missing `OrderID` → 422; unknown session → 404; non-success TranCode → cart `failed` and job still runs (job will not place). Invalid private key / sign failure → `RuntimeException` at initiate. Debug logs payload when `debug` is true.

**Timeouts / retries:** none in RaiAccept classes. Finalize is `dispatchSync` (see [jobs.md](jobs.md)).

**Job vs handler success check:** the job treats success as `TranCode === '000'` (exact string). The handler’s padded `000` check is broader (`0`, `000`).

---

## Driver: PCB Bank (TXPG / QPC)

**Class:** `App\Services\Payment\Drivers\PcbBank\PcbBankDriver`  
**Alias / `GATEWAY`:** `pcbbank`  
**Implements:** `PaymentDriver`, `RedirectPaymentDriver`.

**Helpers**

- `PcbBankClient` — HTTP to TXPG with mutual TLS.
- `PcbBankStatus` — maps TXPG `OrderStatus` to `authorized` / `failed` / `pending`.

**Config keys (names):** `title`, `description`, `image`, `base_url`, `order_type`, `currency`, `language`, `return_url`, `result_url`, `client_cert_pem`, `client_key_pem`, `ca_cert_pem`, `client_cert_path`, `client_key_path`, `ca_cert_path`, `verify_peer`, `debug`.

**Env names used as field defaults:** `PCB_BANK_BASE_URL`, `PCB_BANK_ORDER_TYPE`, `PCB_BANK_RETURN_URL`, `PCB_BANK_RESULT_URL`, `APP_URL`.

**Required at `assertUsable`:** `base_url`, `order_type`, plus client cert and key (PEM **or** readable path).

**Public config:** `title`, `description`, `image`, `language`.

**Called from:** `CartService::setPaymentMethod` (`initiate`); `CheckoutController::placeOrder` (`gatewayRedirect`). `initiate` reuses only a still-`pending` PCB session with same method and amount. `gatewayRedirect` **creates a new TXPG order** if the session is not reusable (failed/refused/cancelled), so the shopper is not sent back to a dead HPP.

**Create order (aligned with vendor WooCommerce TXPG plugin, `pcb bank payment doc/woocommerce_qpc/`):**

- `POST {base_url}/order` JSON `{ order: { typeRid, amount, currency, description, ridByMerchant, language, hppRedirectUrl, initiationEnvKind: Browser, type.paymentMethods: [any], consumerDevice.browser.ip/userAgent } }`
- Vendor plugin: same `Browser` env, `paymentMethods: ['any']`, `hppRedirectUrl`, mutual TLS with `cert.pem` / `key.pem` / `ca.pem`, `CURLOPT_SSL_VERIFYPEER` false. Plugin treats create success when order status is `Preparing`. This driver requires `id`, `password`, and `hppUrl` instead of checking `Preparing`.
- HPP URL: `{hppUrl}/?id={id}&password={password}` (same query shape as the plugin).
- `ridByMerchant`: `ORD{cartId}{YmdHis}` (stored as `merchant_reference`; `cart_payments.reference` is the bank `id`).
- Currency: cart currency or config `currency` default `EUR`. Amount: `round(grand_total, 2)` major units (not minor).
- Email is **not** sent to TXPG (comment: Consumer rejects `email`).

**Lookup:** `GET {base_url}/order/{id}?password=&tokenDetailLevel=2&tranDetailLevel=1` (same query flags as the plugin).

**Webhooks / callbacks:** `PcbBankCallbackHandler`. Reads `ID`/`id` and `STATUS`/`status` (vendor `txpg_webhook` uses GET `ID` and `STATUS`). Authoritative status is `getOrder` over mTLS; `verified` true only if that call succeeds. On lookup failure, logs a warning and keeps the reported `STATUS`. Always appends callback on the cart payment and `FinalizePcbBankOrderJob::dispatchSync` (no terminal-status short-circuit). Return source adds `redirect` to `result_url` (or `{APP_URL}/payment-result` if empty) with `gateway=pcbbank&gateway_order_id=`. Notify has no `notify_text` → JSON acknowledgement. Missing ID: 422; unknown session: 404; both still set `redirect` on return.

**TXPG `OrderStatus` (vendor OpenAPI 4.0.15 enum; mirrored in `PcbBankStatus`):** `Preparing`, `WaitPushTran`, `Authorized`, `PartPaid`, `Funded`, `FullyPaid`, `Expired`, `Cancelled`, `Rejected`, `Refused`, `Closed`.

Mapping in this codebase:

- Success → internal `authorized`: `Authorized`, `PartPaid`, `FullyPaid`, `Funded`, `Closed`
- Failure → `failed`: `Expired`, `Cancelled`, `Rejected`, `Refused`
- Else → `pending`

Vendor WooCommerce “good” statuses used to complete the WC order are `Authorized`, `FullyPaid`, `PartPaid` only. This app also treats `Funded` and `Closed` as success.

**Failure handling:** createOrder throws `RuntimeException` on HTTP error or `errorCode` (`errorMessage` / `errorCode` / `HTTP {status}`). Incomplete create (missing id/password/hpp) throws after a warning log. `getOrder` throws on non-success HTTP; callback catches `Throwable` and continues unverified.

**Timeouts / retries:** `PcbBankClient::send` uses `Http::timeout((int) ($this->config['timeout'] ?? 30))`. There is **no** `timeout` field in `config/payments.php`; the 30s fallback is code-only. No retry loop. TLS: `verify_peer` default false (matches plugin `SSL_VERIFYPEER` false); when true, `verify` is CA PEM/path or `true`. Inline PEMs are written to chmod 0600 temp files and deleted after the request.

---

## Callback handlers (summary)

| Class | Gateway | Verify | Finalize |
| --- | --- | --- | --- |
| `RaiAcceptCallbackHandler` | `raiaccept` | SHA-512 signature vs `bank_public_key_pem` | `FinalizeRaiAcceptOrderJob::dispatchSync` unless cart already terminal |
| `PcbBankCallbackHandler` | `pcbbank` | `PcbBankClient::getOrder` | `FinalizePcbBankOrderJob::dispatchSync` always after persist |
