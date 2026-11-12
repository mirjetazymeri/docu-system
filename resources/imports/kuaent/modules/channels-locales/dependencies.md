# Dependencies

Only references found in code. This module is consumed by catalog, CMS, cart, checkout, and admin UIs.

## Outgoing (this module depends on)

### Framework / packages

| Symbol | Used by |
| --- | --- |
| `Illuminate\Http\Request`, `Illuminate\Support\Facades\Cache`, `Illuminate\Support\Arr`, `Illuminate\Support\Str` | controllers, middleware, `Locale`, `ChannelLocaleService` |
| `Yajra\DataTables\Facades\DataTables` | admin Channel, Locale, Translation controllers |
| `app/Http/Controllers/Controller.php` | all four controllers |
| `check.permission` → `App\Http\Middleware\AutoAuthorizePermission` | admin controllers |
| `role` → `App\Http\Middleware\RoleMiddleware` | admin route group in `routes/web.php` |
| `auth`, `verified.if-required` | same groups |

### App types

| Symbol | Path | Used by |
| --- | --- | --- |
| `App\Models\Channel` | `app/Models/Channel.php` | controllers, service, middleware, helpers, seeders, `database/migrations/2026_06_09_120000_create_channel_locale_table.php` data backfill |
| `App\Models\Locale` | `app/Models/Locale.php` | controllers, service, `AppServiceProvider`, API controller, seeders |
| `App\Models\User` | `app/Models/User.php` | `channels()`, `currentChannel()`, `locale`, `current_channel_id` — ChannelController switch, SetCurrentChannel, ResolveApiChannel, LocaleController switch, ChannelSeeder, helpers, view composer |
| `App\Models\Setting` | `app/Models/Setting.php` | `Channel::settings()` relation only (settings module owns the model) |
| `App\Models\Category` | `app/Models/Category.php` | `Channel::categories()` |
| `App\Models\Product` | `app/Models/Product.php` | `Channel::products()` |
| `App\Models\PaymentMethod` | `app/Models/PaymentMethod.php` | `Channel::paymentMethods()` |
| `App\Models\ShippingMethod` | `app/Models/ShippingMethod.php` | `Channel::shippingMethods()` |
| `config/locales.php` | `config/locales.php` | `ChannelLocaleService::globalLocaleMap`, `TranslationController`, `AppServiceProvider` fallback |
| `config('app.locale')`, `config('app.fallback_locale')`, `config('app.supported_locales')` | `config/app.php` (locale/fallback only) | middleware, service, hydration |
| `lang/{locale}/translations.php` | `lang/` | `TranslationController` |

`Channel` relation targets are other modules’ models; this module’s `Channel` class declares the inverses.

## Incoming (other code depends on this module)

### Middleware applied to other routes

- `routes/api.php` `v1` group: `channel.api`, `locale.api` (all v1 endpoints).
- `routes/web.php` sitemaps: `locale.api`, `channel.api`.
- `SetCurrentChannel` on all `web` routes.
- `set.user.locale` on profile, admin, customer, sales-specialist groups.

### Container `currentChannel`

Bound by this module’s middleware; also rebound by:

- `App\Http\Controllers\SalesSpecialist\Concerns\ResolvesSalesChannel`
- `App\Services\SalesSpecialist\SalesSpecialistOrderService`
- `App\Jobs\FinalizePcbBankOrderJob::bindChannelContext`
- `App\Jobs\FinalizeRaiAcceptOrderJob`
- `tests/Feature/Bundle/BundleTestCase`
- `tests/Feature/Configurable/ConfigurableTestCase`

Readers of `app('currentChannel')` / `app()->bound('currentChannel')` include:

- `app/helpers.php` (`current_channel`, `channel_locales`)
- `App\Http\Middleware\SetLocaleFromRequest`
- `App\Http\Controllers\Api\V1\LocaleController`
- `App\Http\Controllers\Api\V1\CartController`
- `App\Http\Controllers\Api\V1\CategoryController`
- `App\Http\Controllers\Api\V1\AttributeController`
- `App\Http\Controllers\Api\V1\ProductProjectionController`
- `App\Http\Controllers\Api\V1\ProductSearchController`
- `App\Http\Controllers\Api\V1\ProductFacetController`
- `App\Http\Controllers\Api\V1\ProductRentalAvailabilityController`
- `App\Http\Controllers\Api\V1\PaymentMethodController`
- `App\Http\Controllers\Api\V1\CheckoutController`
- `App\Http\Controllers\Api\V1\PageController`
- `App\Http\Controllers\Api\V1\WidgetController`
- `App\Http\Controllers\Api\V1\SettingController`
- `App\Http\Controllers\Api\V1\PromotionController`
- `App\Http\Controllers\Api\V1\WishlistController`
- `App\Http\Controllers\Api\V1\CompareController`
- `App\Http\Controllers\Api\V1\StorefrontEventController`
- `App\Http\Controllers\Api\V1\StorefrontConsentController`
- `App\Http\Resources\Api\V1\PromotionProductResource`
- `App\Http\Requests\SalesSpecialist\StoreOrderRequest`
- `App\Http\Controllers\SitemapController`
- `App\Services\SettingService`
- `App\Services\Checkout\CartService`
- `App\Services\Product\ProductProjectionSearch`
- `App\Services\Product\ProductSynonymSearchService`
- `App\Services\Cms\WidgetRenderer`
- `App\Services\Cms\RandomProductWidgetResolver`
- `App\Services\Cms\TrendingProductWidgetResolver`
- `App\Services\NewsletterSubscriberService`
- `App\Models\Product` (price helpers)

### Helpers `channel_locale_codes()` / `channel_locales()`

- `App\Http\Controllers\Admin\LocaleController::switch`
- `App\Http\Controllers\Admin\ProductSynonymController`
- `App\Providers\AppServiceProvider` view composer (`channel_locales`)
- `App\Services\Product\ProductSynonymSearchService`
- Blade: `resources/views/admin/attributes/_form.blade.php`, `_option_row.blade.php`, `categories/_form.blade.php`, `products/_form.blade.php`, `pages/_form.blade.php`, `widgets/_form.blade.php`, `promotions/_form.blade.php`

`current_channel()`: `app/helpers.php`, `App\Http\Controllers\Api\V1\MenuController`, `App\Support\StorefrontUrl`.

### `App\Models\Channel` constructed or queried

- `App\Http\Controllers\Admin\UserController` (`Channel::all()`, `$user->channels()->sync`)
- `App\Http\Controllers\SalesSpecialist\Concerns\ResolvesSalesChannel`
- `App\Services\SettingService` (fallback `Channel::query()->orderBy('id')`)
- `App\Services\System\SystemCacheService`
- `App\Services\NewsletterSubscriberService`
- `App\Services\SalesSpecialist\SalesSpecialistOrderService`
- `App\Support\StorefrontUrl` (`Channel $channel`, `domain`)
- `App\Console\Commands\PurgeStorefrontTelemetryCommand`
- `App\Jobs\FinalizePcbBankOrderJob`, `App\Jobs\FinalizeRaiAcceptOrderJob`
- `database/seeders/CategorySeeder` (requires channels)
- `database/seeders/BrandAttributeSeeder`
- Feature test base classes above

### `App\Models\Locale`

- `App\Services\Product\ProductProjectionService` (`Locale::activeCodes()`)
- `App\Providers\AppServiceProvider`
- Feature test base classes (`Locale::query()->create`)

### `ChannelLocaleService`

- `App\Http\Controllers\Admin\ChannelController`
- `App\Http\Controllers\Admin\LocaleController`
- `App\Http\Controllers\Api\V1\LocaleController`
- `App\Http\Middleware\SetLocaleFromRequest`
- `app/helpers.php`
- `database/seeders/ChannelSeeder`
- `App\Services\System\SystemCacheService`

### `users.current_channel_id` / `User::currentChannel` (admin scoping)

Controllers with private `currentChannelId(s)` or direct `auth()->user()->current_channel_id` / `currentChannel`:

- `App\Http\Controllers\Admin\CategoryController`
- `App\Http\Controllers\Admin\ProductController`
- `App\Http\Controllers\Admin\AttributeController`
- `App\Http\Controllers\Admin\PageController`
- `App\Http\Controllers\Admin\WidgetController`
- `App\Http\Controllers\Admin\CouponController`
- `App\Http\Controllers\Admin\CatalogRuleController`
- `App\Http\Controllers\Admin\PromotionController`
- `App\Http\Controllers\Admin\ProductSynonymController`
- `App\Http\Controllers\Admin\PaymentMethodController`
- `App\Http\Controllers\Admin\ShippingMethodController`
- `App\Http\Controllers\Admin\SettingController`
- `App\Http\Controllers\Admin\StorefrontEventController`
- `App\Http\Controllers\Admin\NewsletterSubscriberController`
- `App\Http\Requests\Admin\Product\StoreProductRequest`

Models using `auth()->user()->current_channel_id` in scopes: `App\Models\Product`, `Category`, `Attribute`, `Page`, `Widget`, `PaymentMethod`, `ShippingMethod`, `Coupon`, `CatalogRule`, `Promotion`.

### `app()->getLocale()` (set by this module’s middleware on those pipelines)

API/admin/CMS/catalog classes listed in the architecture grep of `app()->getLocale()` / `setLocale`, including:

- `App\Http\Controllers\Api\V1\{Category,Attribute,ProductProjection,ProductSearch,ProductFacet,ProductReview,Page,Widget,Menu,Seo,Promotion}Controller`
- `App\Http\Resources\Api\V1\{Widget,Attribute,CartItem,Page,Promotion,PromotionProduct}Resource`
- `App\Models\{Product,Category,Attribute,AttributeOption,Page,Widget,Promotion}`
- `App\Services\Cms\{WidgetRenderer,RandomProductWidgetResolver,TrendingProductWidgetResolver,PromotionWidgetResolver}`
- `App\Http\Controllers\Admin\{ProductController,CategoryController,LookupController}`
- `App\Http\Controllers\SalesSpecialist\OrderController`
- `App\Http\Controllers\SitemapController`

### `config('locales')`

- `App\Http\Controllers\Admin\TranslationController`
- `App\Providers\AppServiceProvider`
- `database/seeders/PageSeeder`
- `database/seeders/CategorySeeder`
- `database/seeders/DemoCatalogSeeder`
- `database/seeders/BrandAttributeSeeder`
- Blade `config("locales.$locale.name")` in attribute/product/page/widget/promotion forms

### `users.locale`

- `App\Http\Middleware\SetUserLocale`
- `App\Http\Controllers\Admin\LocaleController::switch`
- `App\Http\Controllers\Api\V1\AuthController::register`

### Permissions / UI

- `database/seeders/ModulePermissionSeeder` modules `Channels`, `Locales`, `Translations`
- `resources/views/layouts/components/main-sidebar.blade.php`
- `resources/views/layouts/components/main-header1.blade.php`
- `resources/views/layouts/components/main-header.blade.php`

### Channel assignment UI (users / payments / shipping)

- `App\Http\Controllers\Admin\UserController` + user create/edit views (not listed here as owned)
- `resources/views/admin/partials/_channel_country_select2.blade.php` (Select2 for `.method-form-channels`)
