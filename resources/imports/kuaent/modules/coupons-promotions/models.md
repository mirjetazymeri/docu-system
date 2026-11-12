# Models — Coupons and Promotions

Module index: [README.md](./README.md)

---

## `App\Models\Coupon`

Source: `app/Models/Coupon.php`

### Purpose

A redeemable coupon code with discount type, limits, validity window, and optional catalog/customer-group scoping.

### Table

```text
coupons
```

Pivot tables: `coupon_user_groups`, `coupon_products`, `coupon_categories`. See [database.md](./database.md).

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `id` | bigint PK | |
| `channel_id` | FK nullable | Admin writes always set current channel. Apply-time lookup also allows `null` (legacy global). |
| `code` | string(64) unique | Redemption code |
| `name` | string(191) nullable | Internal label |
| `description` | text nullable | Internal note |
| `type` | string(32) default `by_percent` | `by_percent`, `by_fixed`, `free_shipping` |
| `amount` | decimal(12,2) default 0 | Percent or fixed amount |
| `status` | string(32) default `active` | `active` / `inactive` |
| `usage_limit` | unsigned int nullable | Global applied+consumed cap |
| `usage_limit_per_user` | unsigned int nullable | Per user_id or email |
| `starts_at` / `ends_at` | timestamp nullable | Validity window |
| `min_subtotal` / `max_subtotal` | decimal(12,2) nullable | Cart `row_total` sum gates |
| `max_discount_amount` | decimal(12,2) nullable | Cap after percent/fixed quote |
| `allow_guest` | boolean default true | Guest carts |
| `stackable` | boolean default false | Stored only; not used by `CouponService` |
| `conditions` | json nullable | `{ attributes: [{ attribute_id, option_ids[] }] }` |
| `batch` | string(64) nullable, indexed | Bulk-generation group (`B` + 6 chars) |

### Casts

`amount`, `min_subtotal`, `max_subtotal`, `max_discount_amount` → `decimal:2`; `allow_guest`, `stackable` → `boolean`; `conditions` → `array`; `starts_at`, `ends_at` → `datetime`.

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `channel()` | `belongsTo` | `Channel` | Optional channel |
| `userGroups()` | `belongsToMany` | `UserGroup` | Pivot `coupon_user_groups` |
| `products()` | `belongsToMany` | `Product` | Pivot `coupon_products` |
| `categories()` | `belongsToMany` | `Category` | Pivot `coupon_categories` |
| `usages()` | `hasMany` | `CouponUsage` | Apply/consume rows |

`Cart` has coupon **columns** (`coupon_id`, `coupon_code`, `coupon_discount`) but **no** `coupon()` relation on `App\Models\Cart`.

### Scopes

| Scope | Behavior |
| ----- | -------- |
| `scopeForCurrentChannel` | `where('channel_id', auth()->user()->current_channel_id)` |

### Business Methods

`isActiveNow(): bool` — `status === 'active'`, `starts_at` not in the future, `ends_at` not in the past. Null dates are treated as open.

### Events / Observers

None found.

---

## `App\Models\CouponUsage`

Source: `app/Models/CouponUsage.php`

### Purpose

One usage row per coupon+cart. Status tracks apply → consume (order placed) or cancel (removed).

### Table

```text
coupon_usages
```

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `coupon_id` | FK cascade | |
| `cart_id` | FK nullable, nullOnDelete | |
| `order_id` | unsignedBigInteger nullable | No FK in migration. Set by `OrderService` on place-order. No `order()` relation on the model. |
| `user_id` | FK nullable, nullOnDelete | |
| `email` | string(191) nullable | Guest tracking |
| `amount_discounted` | decimal(12,2) default 0 | Quoted discount |
| `status` | string(32) default `applied` | `applied`, `consumed`, `cancelled` |
| `ip` | string(64) nullable | From `request()->ip()` on apply |
| `user_agent` | text nullable | Truncated to 1000 chars on apply |

### Casts

`amount_discounted` → `decimal:2`.

### Relationships

| Method | Relationship | Related Model |
| ------ | ------------ | ------------- |
| `coupon()` | `belongsTo` | `Coupon` |
| `cart()` | `belongsTo` | `Cart` |
| `user()` | `belongsTo` | `User` |

### Observed schema mismatch

`OrderService` updates `used_at` when consuming a usage. `create_coupon_usages_table` does **not** add `used_at`, and it is not in `$fillable`. Whether that column exists at runtime is **not determinable from the current repository** (no later migration found).

---

## `App\Models\Promotion`

Source: `app/Models/Promotion.php`

### Purpose

A marketing campaign with default (non-translated) title/slug/description, media paths, status, date window, optional channel, translations, and product items.

### Table

```text
promotions
```

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `channel_id` | FK nullable, nullOnDelete | Admin store/update always writes current channel |
| `title` | string | Default title |
| `slug` | string unique | Default slug; auto-slug on create/save |
| `description` | text nullable | Default description |
| `banner_image` / `icon` / `logo` | string nullable | Paths on disk `public` under `promotions/`, or absolute http(s) URLs |
| `status` | string(32) default `active` | `active` / `inactive` |
| `starts_at` / `ends_at` | timestamp nullable | Active window for `scopeActive` / `isActiveNow` |

### Casts

`starts_at`, `ends_at` → `datetime`.

### Relationships

| Method | Relationship | Related Model | Description |
| ------ | ------------ | ------------- | ----------- |
| `channel()` | `belongsTo` | `Channel` | |
| `translations()` | `hasMany` | `PromotionTranslation` | |
| `items()` | `hasMany` | `PromotionItem` | Ordered by `position`, `id` |
| `products()` | `belongsToMany` | `Product` | Via `promotion_items` with pivot price/special/position |

### Scopes

| Scope | Behavior |
| ----- | -------- |
| `scopeActive` | `status = active` and `starts_at` null or `<= now()` and `ends_at` null or `>= now()` |
| `scopeForChannel(?int $channelId)` | If `$channelId` is null, no extra filter. Else `channel_id` null **or** equal to `$channelId` |
| `scopeForCurrentChannel` | Exact `channel_id = auth()->user()->current_channel_id` |

### Business Methods

- `translation(?string $locale = null): ?PromotionTranslation` — requested locale, else `config('app.fallback_locale')`.
- `isActiveNow(): bool` — same window rules as coupons (`ends_at` past is inactive). Note `scopeActive` uses `ends_at >= now()` (inclusive of “now”); `isActiveNow` uses `$this->ends_at->isPast()`.

### Model hooks

`booted()`:

- `creating`: if slug empty, `Str::slug(title ?: uuid)`.
- `saving`: always `Str::slug(slug ?: title ?: uuid)`.

No observers found.

---

## `App\Models\PromotionItem`

Source: `app/Models/PromotionItem.php`

### Purpose

One product on a campaign, with optional special-price override used for API display and for writing onto `product_prices`.

### Table

```text
promotion_items
```

Unique: `(promotion_id, product_id)`.

### Important Fields

| Field | Type (migration) | Purpose |
| ----- | ---------------- | ------- |
| `promotion_id` | FK cascade | |
| `product_id` | FK cascade | |
| `price` | decimal(12,2) nullable | Column exists. `PromotionService::syncItems()` always writes `price => null`. Not used by `resolvePricing()`. |
| `special_price` | decimal(12,2) nullable | Promotion special |
| `special_from` / `special_to` | date nullable | Special window (day resolution in `resolvePricing`) |
| `position` | unsigned int default 0 | Display order |

### Casts

`price`, `special_price` → `decimal:2`; `special_from`, `special_to` → `date`.

### Relationships

| Method | Relationship | Related Model |
| ------ | ------------ | ------------- |
| `promotion()` | `belongsTo` | `Promotion` |
| `product()` | `belongsTo` | `Product` |

### Business Methods

`resolvePricing(?ProductPrice $channelPrice = null): array`

- `base` = channel product `price` (or 0).
- If the item has any of `special_price` / `special_from` / `special_to` set, those fields override product specials (unset item fields fall back to the product price row).
- Otherwise product specials are used.
- `final` = `min(base, special)` when special is active for today (`startOfDay` vs from/to); else `base`.

Returns `base`, `final`, `special_price`, `special_from` (`Y-m-d`), `special_to` (`Y-m-d`).

---

## `App\Models\PromotionTranslation`

Source: `app/Models/PromotionTranslation.php`

### Purpose

Per-locale title, slug, and description. API resources prefer translation over default promotion fields.

### Table

```text
promotion_translations
```

Unique: `(promotion_id, locale)`.

### Important Fields

| Field | Type | Purpose |
| ----- | ---- | ------- |
| `promotion_id` | FK cascade | |
| `locale` | string(10) | Locale code |
| `title` / `slug` | string nullable | |
| `description` | text nullable | |

No casts. `promotion()` belongsTo `Promotion`.

Empty translation payload (all of title/slug/description empty) causes `PromotionService::syncTranslations()` to **delete** that locale row.
