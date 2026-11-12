# Services — External catalog

All classes live in `App\Services\ExternalCatalog`. None are bound in a service provider.

---

## `ExternalCatalogApiService`

**File:** `app/Services/ExternalCatalog/ExternalCatalogApiService.php`

JSON HTTP client for the Elkos Order endpoint. Only method used in-repo: `submitOrder`.

### `submitOrder(array $payload): array`

1. Endpoint name: `(string) config('external_catalog.order_endpoint', 'Order')`.
2. `POST` via `request()` to `buildEndpointUrl($endpoint)`.
3. `->throw()` (Laravel HTTP: unsuccessful status throws).
4. `$response->json()` must be an **array**; otherwise `RuntimeException('External catalog order endpoint returned invalid JSON payload.')`.
5. Returns that array as-is. **No field names of the Elkos response are read in this class.**

### `request(): PendingRequest` (protected)

| Behavior | Code |
|---|---|
| Accept / content | `Http::acceptJson()->asJson()` |
| Timeout | `max(1, (int) config('external_catalog.timeout', 60))` seconds |
| Auth | If trimmed `config('external_catalog.api_key')` is non-empty: header `X-API-KEY`. If empty, **no** auth header |
| Retry | None |
| Connection | Laravel HTTP default |

### `buildEndpointUrl(string $endpoint): string` (protected)

See [architecture.md](architecture.md). Absolute `http://` / `https://` endpoint bypasses base + service path.

---

## `OrderExportService`

**File:** `app/Services/ExternalCatalog/OrderExportService.php`

Constructor: `ExternalCatalogApiService $api`.

### Constants

| Constant | Value |
|---|---|
| `STATUS_PENDING` | `pending` |
| `STATUS_SYNCED` | `synced` |
| `STATUS_FAILED` | `failed` |
| `DEFAULT_DELIVERY_TIME` | `10h-12h` |

### `send(Order $order): array`

Return shape: `{success: bool, response?: array, error?: string}`.

1. `loadMissing(['items', 'addresses', 'payments', 'shipments'])`.
2. Force-fill `external_order_sync_status = pending`, `external_order_sync_error = null`, `save()`.
3. `$payload = buildPayload($order)`.
4. Try `$this->api->submitOrder($payload)`.
5. Success: force-fill `synced`, `external_order_synced_at = now()`, error `null`, `external_order_sync_response = $response`, `save()`. Return `success` + `response`.
6. Catch `Throwable`: `Log::error('External order export failed', order_id, order_number, message)`. Force-fill `failed` + `external_order_sync_error = $e->getMessage()`, `save()`. Return `success: false` + `error`. Does not rethrow.

Called from:

- `SubmitExternalOrderJob::handle` (auto).
- `App\Http\Controllers\Admin\OrderController::sendExternal` (manual, in-process).

Does **not** skip already-synced orders. Manual resend re-POSTs. The **job** skips `synced` before calling `send()`.

### `buildPayload(Order $order): array`

Returns `{ Transaction: { … } }`. Reloads `items.product.bundleItems.childProduct`, `addresses`, `payments`, `shipments`.

| Transaction key | Source |
|---|---|
| `OrderID` | `(string) ($order->number ?? $order->id)` |
| `PaymentType` | Latest payment by `id` desc if `payments` is loaded, else `$order->payment`. Then `method_code` ?: `method_name` ?: `'unknown'` |
| `OrderDate` | `($order->placed_at ?? now())->format('Y-m-d')` |
| `DeliveryDate` | Same date as `OrderDate` |
| `DeliveryTime` | `DEFAULT_DELIVERY_TIME` (`10h-12h`) |
| `ShippingMethod` | Latest shipment by `id` desc: `carrier_name` ?: `service` ?: `carrier_code` ?: `'unknown'` |
| `TotalOrderValue` | `formatAmount($order->grand_total)` |
| `TransactionDetails` | `buildTransactionDetails($order)` |
| `note` | `buildNote($order)` |

Elkos’s required/optional fields beyond this outbound map are **not determinable from the current repository**.

### `buildTransactionDetails(Order $order): list<array{ProductNumber, Quantity, Price}>`

Covered by unit tests. For each `OrderItem`:

- If `resolveBundleComponents` returns `[]`: one line with item `sku`, `(int) qty`, `formatAmount(unit_price)`.
- Else expand components. First component with a non-empty SKU gets `Price = formatAmount(item.unit_price / qtyPerBundle)`; later components `Price = 0`. `Quantity = (int) item.qty * qtyPerBundle` (`qtyPerBundle = max(1, component.quantity)`). Empty-SKU components skipped. If every component lacked a SKU: fall back to the parent line.

### `resolveBundleComponents` (protected)

1. If `data_get($item->meta, 'bundle')` is a non-empty array: `array_values` of elements that `is_array`.
2. Else if `$item->product` exists and `Product::isBundle()`: map `bundleItems` to `{sku: childProduct.sku, quantity: (int) bi.quantity}`.
3. Else `[]`.

### `buildNote(Order $order): string`

Pipe-separated (` | `) parts, empty trimmed parts dropped:

- Literal `FROM WEB ELKOS`
- `EMAIL: ` + `$order->email`
- Optional `CUSTOMER:` from billing (else shipping) full name (`FIRST NAME: first last`), billing `COMPANY`, billing `VAT`, billing/shipping `PHONE`
- Optional `SHIPPING ADDRESS:` from shipping else billing: `address1`, `address2`, `postcode city`, uppercased `country`, `phone`
- Optional `CUSTOMER NOTE:` from `$order->customer_note`
- Optional `BUNDLES:` parent sku × qty `=>` child sku × expanded qty, semicolon-separated

### `formatAmount(mixed $amount): string`

`round((float) $amount, 2)`. If within `0.001` of an integer, return integer string (`400` not `400.00`). Else `number_format(..., 2, '.', '')`.

---

## `ExternalCatalogOrderSettings`

**File:** `app/Services/ExternalCatalog/ExternalCatalogOrderSettings.php`

Comment: Admin → Settings → Product → External order export.

| Constant | Value |
|---|---|
| `MODE_AUTO` | `auto` |
| `MODE_MANUAL` | `manual` |
| `KEY_MODE` | `product.external_order.mode` |

`mode(?int $channelId = null): string` — `SettingService::get(KEY_MODE, MODE_MANUAL, $channelId)` with `$channelId ??= (int) config('catalog.default_channel_id', 1)`. Lowercase trim. Invalid → `manual`.

`isAuto` / `isManual` compare to those constants.

---

## `StorefrontRevalidator`

**File:** `app/Services/ExternalCatalog/StorefrontRevalidator.php`

Static. Class doc: tags should match storefront `CACHE_TAGS` (`lib/api/cache-tags.ts`); unknown tags ignored by Next. That storefront file is **not** in this repository.

Static state: `$pendingTags`, `$flushRegistered` — coalesces multiple `pingAfterCommit` calls into one HTTP POST.

### `pingAfterCommit(?array $tags = null): void`

Tags default `config('external_catalog.revalidate_tags', ['catalog'])`, trimmed, empty dropped. Empty after filter → return.

Merges unique tags into `$pendingTags`. Registers a single flush:

- If `DB::transactionLevel() > 0`: `DB::afterCommit($flush)` and `DB::afterRollback` clears pending + flag.
- Else `$flush()` immediately.

Flush calls `ping($queued)`.

### `ping(?array $tags = null): void`

`attempt($tags)`. If `skipped`, return. If `ok`, `logger()->info('[StorefrontRevalidate] pinged', tags, status)`. Else `logger()->warning('[StorefrontRevalidate] failed: ' . message)`. Never throws.

### `attempt(?array $tags = null): array`

Return keys: `ok`, `skipped`, `status`, `tags`, `body`, `message`.

| Condition | Result |
|---|---|
| Trimmed `revalidate_url` or `revalidate_secret` empty | `skipped: true`, message names env `STOREFRONT_REVALIDATE_URL` / `STOREFRONT_REVALIDATE_SECRET` |
| Tags empty after default/filter | `skipped: true`, message names `STOREFRONT_REVALIDATE_TAGS` |
| HTTP successful | `ok: true`, `status`, `body` if JSON array else `null` |
| HTTP 401 or 403 | `ok: false`, hint “Secret mismatch between backend and storefront.” |
| HTTP 503 | `ok: false`, hint “Storefront REVALIDATE_SECRET may not be loaded (restart the Next.js app).” |
| Other HTTP | `ok: false`, hint “Unexpected response from storefront.” |
| `Throwable` (including timeout) | `ok: false`, `status: null`, `body: null`, message prefixed `Storefront revalidation request failed: ` |

HTTP:

- `Http::timeout(15)`
- Header `x-revalidate-secret` = secret
- `acceptJson()`
- `POST $url` body `['tags' => $tags]`
- No retry, no `->throw()`

### Callers of revalidate (consumers, not this module)

| Caller | Method |
|---|---|
| `App\Services\Product\ProductService::rebuildProjectionsForIds` | `pingAfterCommit()` (default tags) |
| `App\Observers\ProductObserver::deleted` | `pingAfterCommit()` after `ProductProjectionService::deleteByProductIds` |
| `App\Services\Promotion\PromotionService::rebuildProductProjections` | `pingAfterCommit()` (after per-product `rebuild`, including promotion delete) |
| `App\Services\Catalog\CatalogReindexDispatcher::dispatchProjectionRebuilds` | `StorefrontRevalidator::ping()` on Bus batch `then` |
| `App\Services\System\SystemCacheService::revalidateStorefront` | `attempt($tags)` — [settings-system](../settings-system/) |

`App\Console\Commands\VerifyStorefrontRevalidate` imports this class but **does not call it**; it repeats the same HTTP shape (see [commands.md](commands.md)).

---

## Integrations (code only)

### 1. Elkos Order API

| Field | Value |
|---|---|
| **Integration** | Elkos B2B Order endpoint |
| **Purpose** | Export a placed order as `Transaction` JSON |
| **Client class** | `App\Services\ExternalCatalog\ExternalCatalogApiService` (`submitOrder`) |
| **Configuration** | `config/external_catalog.php`: `base_url`, `service_path`, `api_key`, `order_endpoint`, `timeout` |
| **Environment variables (names)** | `EXTERNAL_CATALOG_BASE_URL`, `EXTERNAL_CATALOG_SERVICE_PATH`, `EXTERNAL_CATALOG_API_KEY`, `EXTERNAL_CATALOG_ORDER_ENDPOINT`, `EXTERNAL_CATALOG_TIMEOUT` |
| **Called from** | `OrderExportService::send` only |
| **Authentication method** | Optional request header `X-API-KEY` when `api_key` is non-empty. Empty key → unauthenticated POST |
| **Timeout / retry** | Timeout from config (min 1s). **No** HTTP retry. Job `$tries` does not retry because `send()` swallows throwables |
| **Webhooks** | None inbound |
| **Failure handling** | HTTP/JSON failures become `Throwable` → order `failed` + error message + `Log::error`. Auto job logs `Log::warning('Auto external order export failed')` and completes |

### 2. Next.js storefront ISR revalidate

| Field | Value |
|---|---|
| **Integration** | Storefront revalidate HTTP POST |
| **Purpose** | Bust Next.js ISR tags after catalog/promotion edits or admin cache refresh |
| **Client class** | `App\Services\ExternalCatalog\StorefrontRevalidator` |
| **Configuration** | `revalidate_url`, `revalidate_secret`, `revalidate_tags` |
| **Environment variables (names)** | `STOREFRONT_REVALIDATE_URL`, `STOREFRONT_REVALIDATE_SECRET`, `STOREFRONT_REVALIDATE_TAGS` |
| **Called from** | Table above; admin `POST admin.system_cache.revalidate` and `refreshAll` via `SystemCacheService` |
| **Authentication method** | Header `x-revalidate-secret` (must match storefront `REVALIDATE_SECRET` per comments / command copy — storefront env is not in this repo) |
| **Timeout / retry** | 15 seconds. **No** retry |
| **Webhooks** | None inbound. This is outbound only |
| **Failure handling** | Never throws. Skip if URL/secret/tags missing. Non-success HTTP and exceptions → structured result + warning log on `ping()` |
