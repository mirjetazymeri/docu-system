# Settings & system services

## `SettingService`

**File:** `app/Services/SettingService.php`

Bound as a **singleton** in `AppServiceProvider::register()`.

### Channel resolution — `resolveChannelId($channelId = null): int`

1. Explicit `$channelId` if truthy.
2. Else `app('currentChannel')->id` if the container binding exists and has an id.
3. Else first `Channel` by `id` ascending.
4. Else `0`.

Admin web requests get `currentChannel` from `SetCurrentChannel` (web group). API requests get it from `ResolveApiChannel` (`channel.api`).

### Cache keys

| Method | Key |
|--------|-----|
| `cacheKey($channelId)` | `app_settings_channel_{$channelId}` |
| `apiCacheKey($channelId)` | `api.settings.v1.channel_{$channelId}` |

### `all($channelId = null): array`

`Cache::rememberForever` of `Setting::where('channel_id', $channelId)->pluck('value', 'key')->toArray()`. **Does not merge schema defaults.** Missing keys are simply absent.

### `get(string $key, $default = null, $channelId = null)`

1. `$this->all($channelId)[$key] ?? null`.
2. If still null: `getDefaultFromConfig($key)` then `$default`.
3. If the value is a string and `isJson($string)`, return `json_decode($value, true)`.

`getDefaultFromConfig()` is `data_get(config('settings'), $key)['default'] ?? null`. Schema **`value` is ignored**.

`isJson()` is `json_decode($string); return json_last_error() === JSON_ERROR_NONE`. Numeric strings such as `"1"` decode successfully.

### `setMany(array $data, $channelId = null): void`

For each pair: arrays are `json_encode`d; `Setting::updateOrCreate(['key', 'channel_id'], ['value' => $value])`. Then `clearCache($channelId)`.

No encryption. No write to `type`.

### `clearCache($channelId): void`

`Cache::forget` on both keys above. Does **not** ping the storefront.

---

## Helpers (`app/helpers.php`, Composer files autoload)

| Function | Behavior |
|----------|----------|
| `setting(string $key, $default = null)` | If `SettingService` is unbound, returns `$default`; else `SettingService::get($key, $default)`. |
| `app_name(): string` | `(string) setting('general.app_name', config('app.name', 'Store'))` |
| `setting_file_url(string $key, ?string $fallback = null): string` | `asset('storage/' . ltrim(path))` or `asset($fallback)` or `''` |
| `admin_theme(?string $key = null, mixed $default = null)` | No key → `AdminThemeService` instance; with key → `AdminThemeService::get($key, $default)` |

`tax_config()` is **not** this module; it wraps `TaxSettings` ([tax](../tax/README.md)).

---

## `FooterSections`

**File:** `app/Support/FooterSections.php`

Used by admin `SettingController` and `SettingResource`. Not a service container binding.

| Method | Role |
|--------|------|
| `contactLineTypeOptions()` | Label map: `phone`, `mail`, `address`, `whatsapp`, `facebook`, `instagram`, `x`, `youtube`, `tiktok`, `linkedin`, `pinterest`, `telegram`, `snapchat`, `website`, `skype`, `viber` |
| `contactLineTypeKeys()` | Keys of that map |
| `parse($raw)` | Array, JSON, or newline/comma list → `{ key: slug, label, order }` |
| `decodeColumnContent($raw)` | Array or JSON object |
| `normalizeColumnContentInput($raw)` | Blocks keyed by section **label** with `text` + `items` |
| `sanitizeColumnContentInput(array $input)` | Persist shape: drop empty blocks; trim |
| `validateColumnContentInput(array $columnContent)` | `ValidationException` if a present contact row has empty/invalid `type` |
| `normalizeColumnContentForApi($raw, ?Collection $sections)` | API shape keyed by `section_key`, plus `section_label`, optional `text`/`lines`/`items` |

API default section list if `footer.sections` missing: `"Company\nCustomer Care\nLegal\nSocial"` (hardcoded fallback inside `normalizeColumnContentForApi` when `$sections` is null).

---

## `AdminThemeService`

**File:** `app/Services/AdminThemeService.php`

Singleton in `AppServiceProvider`. Cache key constant `admin_theme_options`.

### `defaults(): array`

`config('admin_theme.defaults', [])`.

| Key | Default |
|-----|---------|
| `theme_mode` | `light` |
| `direction` | `ltr` |
| `nav_layout` | `vertical` |
| `nav_style` | `''` |
| `vertical_style` | `default` |
| `page_style` | `flat` |
| `width` | `fullwidth` |
| `menu_styles` | `transparent` |
| `header_styles` | `transparent` |
| `menu_position` | `fixed` |
| `header_position` | `fixed` |
| `loader` | `disable` |
| `menu_bg_image` | `''` |
| `primary_color` | `#097c67` |
| `secondary_color` | `#ff49cd` |
| `background_color` | `''` |

Allowed HTML option values: `config('admin_theme.options')`. Color swatches: `primary_presets`, `background_presets`. `config('admin_theme.toggled')` exists for sidemenu close-state tokens; **`attributesFor()` never emits `data-toggled`** (comment: `defaultmenu.js` `toggleSidemenu()` would invert layout on boot).

### `all(): array`

`Cache::rememberForever`: latest row `options` or `[]`. On any `Throwable` (e.g. missing table), uses `[]`. Result is `array_merge(defaults, stored)`.

### `get(string $key, mixed $default = null): mixed`

`$this->all()[$key] ?? $default ?? defaults[$key]`.

### `save(array $options): void`

Keeps only default keys; trims strings; merges onto `all()`; update latest row or `create`. Then `Cache::forget(CACHE_KEY)`.

### `reset(): void`

`AdminThemeSetting::query()->delete()` + forget cache.

### `attributesFor(array $theme): array` / `htmlAttributes()` / `htmlAttributeString()`

Maps saved options to HTML attributes:

| Attribute | Source |
|-----------|--------|
| `dir` | `direction` or `ltr` |
| `data-theme-managed` | always `'1'` |
| `data-nav-layout` | `nav_layout` or `vertical` |
| `data-theme-mode` | `theme_mode` or `light` |
| `data-header-styles` | `header_styles` or `transparent` |
| `data-menu-styles` | `menu_styles` or `transparent` |
| `data-page-style` | `page_style` or `flat` |
| `data-width` | `width` or `fullwidth` |
| `data-menu-position` | `menu_position` or `fixed` |
| `data-header-position` | `header_position` or `fixed` |
| `loader` | `loader` or `disable` |
| `data-nav-style` | horizontal: `nav_style` or `menu-click`; vertical **with** non-empty `nav_style`: that style (no `data-vertical-style`) |
| `data-vertical-style` | vertical **without** `nav_style`: `vertical_style` or `default` |
| `data-bg-img` | only if `menu_bg_image` non-empty |

`htmlAttributeString()` HTML-escapes values with `e()`.

Applied in `resources/views/layouts/master.blade.php` on `<html>`. RTL Bootstrap file: `resources/views/layouts/components/styles.blade.php` when `admin_theme('direction') === 'rtl'`. Flat page container class when `page_style === 'flat'`.

### `cssVariables()` / `styleAttribute()` / `hexToRgb()` / `lightenRgb()`

If primary/secondary hex parse: `--primary-rgb` / `--secondary-rgb` as `r, g, b`.

If background hex parses: `--body-bg-rgb`, `--body-bg-rgb2` and `--light-rgb` = RGB + 14 (capped 255), `--form-control-bg` / `--gray-3` as `rgb(...)`, `--input-border` = `rgba(255,255,255,0.1)`.

`hexToRgb`: 3 or 6 hex digits, optional `#`; otherwise `null`.

Injected as `<style id="admin-theme-vars">` when `styleAttribute()` is non-empty.

---

## `SystemCacheService`

**File:** `app/Services/System/SystemCacheService.php`

Not explicitly bound; Laravel auto-resolves.

### `clearApplicationCaches(): array`

Returns `{ ok, steps, message }`.

1. `Artisan::call('cache:clear')` — on `Throwable`, `ok: false` with the exception message.
2. `clearStorefrontDataCaches()` (private).
3. Success message: `Application and API response caches cleared.`

### `clearStorefrontDataCaches(): list<string>` (private)

For each `Channel` id:

- `SettingService::clearCache((int) $channelId)`
- `flushCacheTags(["channel:{$channelId}", 'categories'])`

Then:

- `cache()->forget('products:dataset_hash')`
- `Cache::forget` for `locales.active`, `app:active_locales`, `api.locales.v1`, `catalog:pp_fulltext_index`
- `app(ChannelLocaleService::class)->flushApiCache()` (all channels; see [channels-locales](../channels-locales/README.md))

Steps appended: per-channel settings/category caches; locale API caches; dataset hash caches.

`flushCacheTags`: `Cache::tags($tags)->flush()`, on `Throwable` tries each tag alone; inner failures are swallowed (drivers without tag support).

### `optimizeApplication(): array`

`Artisan::call('optimize:clear')` then `Artisan::call('optimize')`. Success message: `Application optimized successfully.`

### `revalidateStorefront(?array $tags = null): array`

**`StorefrontRevalidator::attempt($tags)`** — [external-catalog](../external-catalog/README.md). This module does not HTTP-call the storefront itself.

When `$tags` is null, `StorefrontRevalidator` uses `config('external_catalog.revalidate_tags')`. Admin UI always calls with no tags.

### `refreshAll(): array`

`clearApplicationCaches` → `optimizeApplication` → `revalidateStorefront`. Stops on first `ok: false` from clear/optimize.

Final `ok` is revalidate `ok` **or** `skipped`. Messages:

| Condition | `message` |
|-----------|-----------|
| revalidate ok | `Caches cleared, application optimized, and storefront revalidated.` |
| skipped | `Caches cleared and application optimized. Storefront revalidation is not configured.` |
| otherwise | `Caches cleared and optimized, but storefront revalidation failed.` |

Includes `revalidate` key with the attempt payload.

### `status(): array`

| Key | Source |
|-----|--------|
| `cache_driver` | `(string) config('cache.default', 'file')` — Laravel 11 env name **`CACHE_STORE`**, default in `config/cache.php` is `database` not `file` |
| `revalidate_enabled` | both `external_catalog.revalidate_url` and `revalidate_secret` non-empty after trim |
| `revalidate_url` | trimmed URL or `null` |
| `revalidate_tags` | trimmed non-empty tags from `external_catalog.revalidate_tags` |

Does not return the secret.

---

## Typed readers (not owned)

These wrap `SettingService::get` / `setting()` for other modules. Documented so operators know where keys are consumed:

| Class | Keys |
|-------|------|
| `App\Support\AuthSettings` | `notifications.user.email_verification`, `notifications.user.password_reset`, `general.admin_register_enabled` |
| `App\Services\Tax\TaxSettings` | `tax.*`, fallback `store.country` |
| `App\Services\Product\CatalogFacetSettings` | `product.catalog.facets.show_*` |
| `App\Services\Product\CatalogSearchSettings` | `product.catalog.search.*` |
| `App\Services\ExternalCatalog\ExternalCatalogOrderSettings` | `product.external_order.mode` |
| `App\Support\CheckoutAddressFields` | `checkout.*`, fallback `system.checkout` |
