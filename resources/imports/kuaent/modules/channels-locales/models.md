# Models

## `App\Models\Channel`

Path: `app/Models/Channel.php`

Table: `channels` (default). Extends `Illuminate\Database\Eloquent\Model`. No `$casts`, no `$hidden`.

Fillable: `name`, `code`, `domain`.

### Relations defined on `Channel`

| Method | Type | Related | Pivot / FK |
| --- | --- | --- | --- |
| `users()` | `belongsToMany(User::class)` | `App\Models\User` | default `channel_user` |
| `settings()` | `hasMany(Setting::class)` | `App\Models\Setting` | `settings.channel_id` |
| `categories()` | `belongsToMany(Category::class)` | `App\Models\Category` | default `category_channel` |
| `products()` | `belongsToMany(Product::class, 'product_channel')` | `App\Models\Product` | `product_channel` |
| `paymentMethods()` | `belongsToMany(PaymentMethod::class, 'payment_method_channel')` | `App\Models\PaymentMethod` | `payment_method_channel` |
| `shippingMethods()` | `belongsToMany(ShippingMethod::class, 'shipping_method_channel')` | `App\Models\ShippingMethod` | `shipping_method_channel` |
| `locales()` | `belongsToMany(Locale::class, 'channel_locale')->withPivot(['is_default'])->withTimestamps()` | `App\Models\Locale` | `channel_locale` |

There is no `Channel` inverse declared for carts, coupons, promotions, projections, and other `channel_id` tables; those models `belongsTo(Channel::class)` on their own side. See [dependencies.md](dependencies.md).

## `App\Models\Locale`

Path: `app/Models/Locale.php`

Table: `locales`. Extends `Illuminate\Database\Eloquent\Model`.

Fillable: `code`, `name`, `flag`, `is_active`, `is_default`.

Casts: `is_active` → boolean, `is_default` → boolean.

### Relations

| Method | Type | Pivot |
| --- | --- | --- |
| `channels()` | `belongsToMany(Channel::class, 'channel_locale')->withPivot(['is_default'])->withTimestamps()` | `channel_locale` |

### `Locale::activeCodes(): array`

`cache()->remember('app:active_locales', 300)` of active codes, ordered by `is_default` desc. Used by `App\Services\Product\ProductProjectionService` (not by the locale API controller).

### `booted()` saving hook

If the locale being saved has `is_default` true, all other rows are updated to `is_default = false`. There is no database unique constraint on `is_default`. Unchecking default on the last default locale is not compensated in this hook.

## `App\Models\User` (fields/relations used here)

Path: `app/Models/User.php`

Fillable includes `locale` and `current_channel_id`.

| Method | Type |
| --- | --- |
| `channels()` | `belongsToMany(Channel::class)` → `channel_user` |
| `currentChannel()` | `belongsTo(Channel::class, 'current_channel_id')` |

`users.locale` is a string column (migration length 5, default `'en'`). Admin switch stores `strtolower` codes with `_` replaced by `-`. API register (`App\Http\Controllers\Api\V1\AuthController`) accepts `locale` max 10 and stores `locale` or `config('app.locale')`.

No `Channel`/`Locale` factories exist. `database/factories/UserFactory.php` does not set `locale` or `current_channel_id`.
