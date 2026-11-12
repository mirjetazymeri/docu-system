# Orders — customer API

Base: `GET /api/v1/...`  
Middleware: `channel.api`, `locale.api`, `auth:sanctum`, `verified.if-required`.

Orders are scoped with `where('user_id', $request->user('sanctum')->id)`. Guest checkouts (`user_id` null) are **not** listed. There is no channel column on `orders`; the resolved channel does not filter this list.

Money fields from resources are **decimal strings** via `App\Support\MoneyFormat::decimal` (and nested breakdown objects on detail `totals`).

## `GET /api/v1/me/orders`

Controller: `MyOrderController@index`  
Request: `ListMyOrdersRequest` ([requests-validation.md](requests-validation.md))  
Resource: `OrderSummaryResource` collection (Laravel paginator `data` + `links` + `meta`). `appends($request->query())`.

### Query

| Param | Default | Behavior |
|---|---|---|
| `per_page` | 20 | 1–100 |
| `page` | 1 | Laravel paginator (not in FormRequest; still works) |
| `number` | — | `WHERE number LIKE %trim%` |
| `status` | — | comma-separated list → `whereIn('status', …)` after trim; empty tokens dropped |
| `date_from` | — | `whereDate(placed_at, >=)` |
| `date_to` | — | `whereDate(placed_at, <=)` |
| `sort` | `placed_at_desc` | see below |

Sort (`match`; unknown values use default):

| `sort` | SQL |
|---|---|
| `placed_at_desc` (default) | `placed_at DESC, id DESC` |
| `placed_at_asc` | `placed_at ASC, id ASC` |
| `total_desc` | `grand_total DESC, id DESC` |
| `total_asc` | `grand_total ASC, id ASC` |

### Summary item shape (`OrderSummaryResource`)

```
id, number, status, currency, items_count,
subtotal, discount_total, shipping_total, deposit_total, tax_total, grand_total,
placed_at, created_at
```

`placed_at` / `created_at` are ISO-8601 or null. `items_count` from `withCount('items')`.

---

## `GET /api/v1/me/orders/{id}`

Controller: `MyOrderController@show`  
Resource: `OrderResource`.

`{id}` is the primary key. Wrong user or missing → 404.

### `include` (optional)

Comma-separated. Allowed: `items`, `addresses`, `payments`, `shipments`. Unknown tokens are ignored.

Not allowed: `creditNotes` / `credit_notes` (resource supports `credit_notes` only if the relation is already loaded).

### Detail shape (`OrderResource`)

Always:

| Field | Source |
|---|---|
| `id`, `number`, `status`, `currency` | order |
| `totals` | `FinancialTotalsResource::fromOrder()` — nested net/tax/gross for subtotal/discount/shipping, `deposit`, `total`, `tax_breakdown`, plus `paid`, `refunded`, `outstanding`, and flat `*_gross` aliases |
| `subtotal`, `discount`, `shipping`, `deposit_total`, `tax`, `grand_total` | aliases of those flat totals |
| `prices_include_tax` | bool (default true if null) |
| `tax_breakdown` | from `totals` |
| `placed_at` | ISO-8601 |

`FinancialTotalsResource::fromOrder()` uses stored order money columns and `sum(items.tax)` when items are loaded (shipping tax ≈ `tax_total - items tax`). `paid` / `refunded` / `outstanding` call `Order::totalPaid()` / `totalRefunded()` / `outstandingAmount()`.

When included:

**addresses:** `{ id, type, data }`

**items:** `id`, `product_id`, `line_type` (default `sale`), sku, name, qty, `unit_price`, `row_total`, `discount`, `tax`, `net`, `gross`, `deposit_total`, rental date/period fields, `meta`. Net prefers `meta.net_total`.

**payments:** `id`, `payment_method_id`, `method_code`, `method_name`, `status`, `amount`, `currency`, `data`, `created_at`. **`reference` is not exposed.**

**shipments:** `id`, `status`, `carrier_code`, `carrier_name`, `service`, `amount`, `eta_text`, `tracking_number`, `meta`, `created_at`. **`tracking_url`, `shipped_at`, `delivered_at` are not exposed.**

---

## Related APIs (not this module)

- Place order: checkout — [cart-checkout](../cart-checkout/)
- Payment return/notify: [payments](../payments/)
- Storefront TypeScript client (`getMyOrders`) lives under `docs-storefront/` (storefront repo docs), not this folder
