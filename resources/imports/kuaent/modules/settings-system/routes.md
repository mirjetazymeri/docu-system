# Settings & system routes

## Admin group

**File:** `routes/web.php`

Group middleware and prefix:

- Middleware: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`
- Prefix: `admin`
- Name prefix: `admin.`

Web stack also appends `App\Http\Middleware\SetCurrentChannel` (`bootstrap/app.php`), which binds `currentChannel` for `SettingService`.

Per-action: `check.permission:{name}` on the controllers (`App\Http\Middleware\AutoAuthorizePermission`). Super Admin (`User::isSuperAdmin()`) skips the permission check.

### Settings

| Method | URI | Name | Action |
|--------|-----|------|--------|
| GET | `/admin/settings` | `admin.settings.index` | `SettingController@index` |
| PUT | `/admin/settings` | `admin.settings.update` | `SettingController@update` |
| POST | `/admin/settings/test-email` | `admin.settings.test-email` | `SettingController@testEmail` |

### Admin theme

| Method | URI | Name | Action |
|--------|-----|------|--------|
| GET | `/admin/theme` | `admin.theme.edit` | `AdminThemeController@edit` |
| PUT | `/admin/theme` | `admin.theme.update` | `AdminThemeController@update` |
| POST | `/admin/theme/reset` | `admin.theme.reset` | `AdminThemeController@reset` |

### System cache

| Method | URI | Name | Action |
|--------|-----|------|--------|
| GET | `/admin/system-cache` | `admin.system_cache.index` | `SystemCacheController@index` |
| POST | `/admin/system-cache/clear` | `admin.system_cache.clear` | `SystemCacheController@clear` |
| POST | `/admin/system-cache/optimize` | `admin.system_cache.optimize` | `SystemCacheController@optimize` |
| POST | `/admin/system-cache/revalidate` | `admin.system_cache.revalidate` | `SystemCacheController@revalidate` |
| POST | `/admin/system-cache/refresh-all` | `admin.system_cache.refresh_all` | `SystemCacheController@refreshAll` |

`refresh-all` Blade form uses `onsubmit` confirm (`translations.admin.system_cache.full_refresh_confirm`).

## Public API

**File:** `routes/api.php`

`Route::prefix('v1')->middleware(['channel.api', 'locale.api'])`. Laravel `withRouting(api: ...)` default prefix is `/api`.

| Method | URI | Name | Action |
|--------|-----|------|--------|
| GET | `/api/v1/settings` | *(unnamed)* | `App\Http\Controllers\Api\V1\SettingController@index` |

No auth. Channel from `ResolveApiChannel` (`?channel=`, `X-Channel`, authenticated `currentChannel`, then `Channel` code `default`). Missing channel → 400 from the controller.

## Permissions

Seeded in `database/seeders/ModulePermissionSeeder.php` as `{resource}_{action}`:

| Module name (seeder) | Permissions |
|----------------------|-------------|
| Settings | `setting_view`, `setting_edit` |
| Theme | `theme_view`, `theme_edit` |
| System Cache | `system_cache_view`, `system_cache_edit` |

Access-level wiring: [users-access-control](../users-access-control/README.md).

## Artisan commands invoked at runtime (not route names)

`SystemCacheService` calls:

| Artisan | When |
|---------|------|
| `cache:clear` | `clearApplicationCaches()` / start of `refreshAll()` |
| `optimize:clear` then `optimize` | `optimizeApplication()` / middle of `refreshAll()` |

This module does **not** register or call `storefront:verify-revalidate`.

## Unused import (not this module)

`routes/web.php` has `use App\Http\Controllers\Admin\PreferenceController;` with **no** references and **no** `PreferenceController.php` on disk. It is unrelated to settings, theme, or system-cache routes.
