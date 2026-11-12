# Sales specialist — controllers

Namespace `App\Http\Controllers\SalesSpecialist`. All extend `App\Http\Controllers\Controller`.

Views live under `resources/views/sales-specialist/`. Layout: `layouts.master`.

## `DashboardController`

No constructor injection.

### `index()`

1. `$specialistId = (int) auth()->id()`.
2. `$baseQuery = Order::query()->ownedBySalesSpecialist($specialistId)`.
3. Stats (cloned queries):

| Key | Query |
|---|---|
| `total` | `count()` |
| `pending` | `where('status', Order::STATUS_PENDING)` |
| `processing` | `where('status', Order::STATUS_PROCESSING)` |
| `completed` | `whereIn('status', [STATUS_COMPLETED, STATUS_SHIPPED, STATUS_PAID])` |

4. `$recentOrders`: `with(['user'])`, `withCount('items')`, `latest('placed_at')`, `limit(8)`.
5. View `sales-specialist.dashboard` with `stats`, `recentOrders`.

The dashboard Blade also reads `auth()->user()` and `$currentChannel ?? auth()->user()->currentChannel`. `currentChannel` is supplied by the global view composer in `AppServiceProvider`, not by this action.

## `CustomerController`

No constructor injection.

### `search(Request $request)`

Query: `q` (trimmed string, default `''`).

`User::role('Customer')`, optional `name`/`email` `LIKE %q%`, `orderBy('name')`, `limit(20)`, columns `id`, `name`, `email`.

JSON:

```
{ "results": [ { "id", "text": "{name} ({email})", "email", "name" } ] }
```

Empty `q` still returns up to 20 customers.

### `store(StoreCustomerRequest $request)`

Inside `DB::transaction`:

1. `User::create`: `name`, `email`, `password` = `str()->random(32)` (hashed by the `User` cast), `email_verified_at` = `now()`.
2. `Role::firstOrCreate(['name' => 'Customer', 'guard_name' => 'web'])` then `assignRole`.
3. If `class_exists(UserGroup::class)` (the model exists in this app): `UserGroup::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer'])` then `groups()->syncWithoutDetaching`.

Validated `phone` is **returned** in JSON and **not** written to `users` (`User::$fillable` has no `phone`).

HTTP **201**:

```
{ "id", "text", "email", "name", "phone": $data['phone'] ?? null }
```

`UserObserver::created` still runs (welcome + admin new-user mail after commit). See [`../auth/README.md`](../auth/README.md).

## `OrderController`

Uses trait `ResolvesSalesChannel`.

Constructor: `SalesSpecialistOrderService $orderService`.

### `index(Request $request)`

| Query | Behavior |
|---|---|
| `q` | Trimmed. If non-empty: `number` or `email` `LIKE`. |
| `status` | If truthy: `where('status', $status)`. |
| `per_page` | `min(100, max(10, (int) default 20))`. The index filter form does **not** expose this field. |

Scope: `ownedBySalesSpecialist((int) auth()->id())`. Eager load `user`, `withCount('items')`, `latest('id')`, `paginate` + `appends`.

View `sales-specialist.orders.index` with `orders`, `q`, `status`. Status dropdown uses `Order::STATUS_LABELS`.

### `create(PaymentDriverManager $paymentDrivers)`

`$paymentDrivers` is unused.

- `$currentChannel = $this->currentChannelModel()`.
- `$guestCheckoutEnabled = CheckoutAddressFields::guestCheckoutEnabled()`.
- `$selectedCustomer`: `User::role('Customer')->find(old('customer_id'))` if old input has `customer_id`.
- `$customerMode`: old `customer_mode` if `existing` or `guest`; else `existing` when a selected customer exists, else `guest` if guest checkout enabled, else `existing`. Guest forced to `existing` when guest checkout is off.
- `$billingValues`: old `billing`; if selected customer and first/last name empty, merge `PersonName::split($selectedCustomer->name)`.
- `$oldItems`: old `items` or `[['product_id' => '', 'qty' => 1]]`.
- `$defaultCountry`: `strtoupper((string) setting('store.country', 'AL'))`.
- `$shippingValues`: old `shipping` or `[]`.

View `sales-specialist.orders.create`.

### `cartPreview(PreviewCartRequest $request)`

`resolveCustomer($data)` then `orderService->previewCart`. `RuntimeException` → JSON `{ message }` **422**. Else JSON preview payload (see [services.md](services.md)).

### `fulfillmentOptions(PreviewFulfillmentRequest $request)`

Same pattern with `previewFulfillment`.

### `store(StoreOrderRequest $request)`

`resolveCustomer` then `placeForCustomer`. `RuntimeException` → `back()->withInput()->withErrors(['order' => message])`. Success → redirect `sales-specialist.orders.show` with flash `success` = `Order created successfully.`

### `show(Order $order)`

`authorizeOwnership($order)` then load `user`, `items`, `addresses`, `payments`, `shipments`.

Billing/shipping arrays: first address with `type` `billing` / `shipping`, `data` or `[]`.

View `sales-specialist.orders.show`. Reuses admin partials `_items_table`, `_totals`, `_customer`, `_status_badge`; specialist partials `_payments_readonly`, `_shipments_readonly`, `_addresses_readonly`.

Implicit `{order}` binding is global `Order` (including other specialists). Ownership is **after** bind: wrong owner → **403** `You do not have access to this order.` Missing/soft-deleted → Laravel **404**.

### `searchProducts(Request $request)`

Routed as `sales-specialist.products.search`. **Not** called by `orders/_create_scripts.blade.php`.

| Query | Behavior |
|---|---|
| `ids` | Comma-separated integers; if any, `whereIn('id', $ids)` (ignores `q`). |
| `q` | Used only when `ids` is empty and `q !== ''`: SKU `LIKE` or translation `name` `LIKE`. |

If both `ids` and `q` are empty, the query is unfiltered beyond channel + `active` (latest 30).

Base: `Product::forChannel($this->resolveChannelId())->where('status', 'active')`, select `id`,`sku`, translations for `app()->getLocale()`, `orderByDesc('id')`, `limit(30)`.

JSON `results[]`: `id`, `text` (`sku — {translation name or sku}`), `sku`.

### `searchShippingMethods(Request $request)` — **no route**

`ShippingMethod::active()->forChannel($this->resolveChannelId())`, optional name/code `LIKE`, `orderBy('sort_order')->orderBy('name')`, `limit(30)`.

JSON `results[]`: `id`, `text` (name), `code`.

### `searchPaymentMethods(Request $request, PaymentDriverManager $paymentDrivers)` — **no route**

Active channel methods, optional name/code filter, ordered, then `filter` to drop `RedirectPaymentDriver` (unknown driver → drop). `take(30)`.

If that collection is **empty**, reload active channel methods **without** the redirect filter (`limit(30)`).

JSON `results[]`: `id`, `text` (name), `code`.

Fulfillment preview does **not** use this fallback; it only returns methods that pass `!RedirectPaymentDriver` and `assertUsable`.

### Protected helpers

| Method | Behavior |
|---|---|
| `resolveCustomer(array $data): ?User` | `guest` or (no mode and no `customer_id`) → `null`. No `customer_id` → `null`. Else `User::role('Customer')->findOrFail($customerId)`. |
| `authorizeOwnership(Order $order): void` | `(int) $order->sales_specialist_id !== (int) auth()->id()` → `abort(403, ...)`. |

## Trait `Concerns\ResolvesSalesChannel`

Used only by `OrderController`.

### `resolveChannelId(): int`

1. If `app()->bound('currentChannel')` and that object has `id` → return it.
2. Else if `$user->current_channel_id` → return it.
3. Else `$user->channels()->first() ?? Channel::query()->orderBy('id')->first()`.
4. No channel → `abort(422, 'No sales channel is configured. Contact an administrator.')`.
5. If the user had no `current_channel_id`: `update` it, `channels()->syncWithoutDetaching([$channel->id])`.
6. `app()->instance('currentChannel', $channel)`.

### `currentChannelModel(): ?Channel`

Calls `resolveChannelId()`, then returns `app('currentChannel')` if bound, else `null`.

## Blade inventory

| View | Role |
|---|---|
| `dashboard.blade.php` | Stat cards + recent orders + quick actions |
| `orders/index.blade.php` | Filter + table + pagination |
| `orders/create.blade.php` | Tabbed form + new-customer modal |
| `orders/_create_scripts.blade.php` | Select2 + preview/fulfillment AJAX |
| `orders/_address_fields.blade.php` | Billing/shipping fields gated by `CheckoutAddressFields::show/required` |
| `orders/_order_summary.blade.php` | Live totals card |
| `orders/show.blade.php` | Read-only order |
| `orders/_payments_readonly.blade.php` | Payment rows |
| `orders/_shipments_readonly.blade.php` | Shipment rows |
| `orders/_addresses_readonly.blade.php` | Billing/shipping dump |

Shared chrome (not owned here): `layouts/components/main-sidebar.blade.php` (dashboard / my orders / create order when `hasRole('Sales Specialist')`), `main-header1.blade.php` (home URL).
