# Routes

## API (`routes/api.php`)

`bootstrap/app.php` loads `routes/api.php` with Laravel’s default `/api` prefix.

Outer group:

```php
Route::prefix('v1')->middleware(['channel.api', 'locale.api'])->group(...)
```

Aliases (`bootstrap/app.php`): `channel.api` → `App\Http\Middleware\ResolveApiChannel`; `locale.api` → `App\Http\Middleware\SetLocaleFromRequest`. Channel resolution: [channels-locales](../channels-locales/README.md).

These API routes have **no** `->name(...)`.

### Public (no `auth:sanctum`)

| Method | URI | Controller | Extra middleware |
|--------|-----|------------|------------------|
| GET | `/api/v1/storefront/consent` | `StorefrontConsentController@show` | — |
| POST | `/api/v1/storefront/consent` | `StorefrontConsentController@store` | — |
| DELETE | `/api/v1/storefront/consent` | `StorefrontConsentController@revoke` | — |
| POST | `/api/v1/storefront/events` | `StorefrontEventController@store` | `throttle:120,1` |

Optional Sanctum: controllers call `$request->user('sanctum')` if a bearer token is present.

### Authenticated

Nested:

```php
Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('verified.if-required')->group(function () {
        // …
        Route::get('me/privacy/export', [PrivacyController::class, 'export']);
        Route::delete('me/privacy/tracking', [PrivacyController::class, 'destroyTracking']);
    });
});
```

| Method | URI | Controller |
|--------|-----|------------|
| GET | `/api/v1/me/privacy/export` | `PrivacyController@export` |
| DELETE | `/api/v1/me/privacy/tracking` | `PrivacyController@destroyTracking` |

`verified.if-required` → `App\Http\Middleware\EnsureEmailIsVerifiedIfRequired`.

Related (not this module): `GET /api/v1/settings` exposes public `gdpr` keys via `SettingResource`.

---

## Admin web (`routes/web.php`)

Group:

```
middleware: auth, verified.if-required, set.user.locale, role:Admin
prefix: admin
name: admin.
```

`web` group also appends `SetCurrentChannel`. CSRF applies.

Comment in `web.php`: `// Storefront Events (analytics, append-only)` — **delete exists**; the comment is not accurate.

| Method | URI | Name | Action | Permission (controller middleware) |
|--------|-----|------|--------|--------------------------------------|
| GET | `/admin/storefront-events` | `admin.storefront_events.index` | `index` | `storefront_event_view` |
| GET | `/admin/storefront-events/{id}` | `admin.storefront_events.show` | `show` | `storefront_event_view` |
| DELETE | `/admin/storefront-events/{id}` | `admin.storefront_events.delete` | `delete` | `storefront_event_delete` |

`{id}` is unconstrained (integer in the controller signature). Blade delete forms use `@method('DELETE')`.

### Sidebar

`resources/views/layouts/components/main-sidebar.blade.php`: under **Pages**, `@can('storefront_event_view')`, link `admin.storefront_events.index`, label `translations.admin.sidebar.storefront_events`. Parent slide visible with `@canany(['page_view', 'widget_view', 'storefront_event_view'])`.

### Permissions seeder

`ModulePermissionSeeder`: module name `Storefront Events`; permissions `storefront_event_view`, `storefront_event_delete` (`guard_name` `web`). No `create` / `edit` permissions.

---

## Console

`routes/console.php`: **no** `gdpr:purge-telemetry` schedule. Command is still available via Artisan discovery. See [commands.md](commands.md).
