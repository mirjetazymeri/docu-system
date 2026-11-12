# Newsletter and contact — Routes

Module README: [README.md](README.md) · API payloads: [api.md](api.md)

Laravel loads `routes/api.php` via `bootstrap/app.php` `withRouting(api: ...)`, which prefixes URIs with `/api`. Web admin routes are in `routes/web.php`.

## API (`routes/api.php`)

Group:

```text
Route::prefix('v1')->middleware(['channel.api', 'locale.api'])
```

These three routes are **not** inside `Route::middleware('auth:sanctum')`.

`channel.api` → `App\Http\Middleware\ResolveApiChannel`. `locale.api` → `App\Http\Middleware\SetLocaleFromRequest`. Newsletter/contact handlers do not call `app()->getLocale()`.

| Method | URI | Name | Controller | Extra middleware | Permission |
| ------ | --- | ---- | ---------- | ---------------- | ---------- |
| POST | `/api/v1/newsletter/subscribe` | *(unnamed)* | `App\Http\Controllers\Api\V1\NewsletterController::subscribe` | group only | none |
| POST | `/api/v1/newsletter/unsubscribe` | *(unnamed)* | `NewsletterController::unsubscribe` | group only | none |
| POST | `/api/v1/contact` | *(unnamed)* | `App\Http\Controllers\Api\V1\ContactController::send` | `throttle:10,1` | none |

There is no GET for subscribers or contact history.

`ResolveApiChannel` resolution order: query `channel` (matched to `channels.code`), header `X-Channel`, `auth()->user()->currentChannel` if `auth()->check()`, else `Channel::where('code', 'default')->first()`. Result is bound as `app('currentChannel')` (may be `null`).

---

## Web admin (`routes/web.php`)

Group:

```text
Route::middleware(['auth', 'verified.if-required', 'set.user.locale', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
```

`role:Admin` is `App\Http\Middleware\RoleMiddleware` (`hasAnyRole('Admin')`). Additional permissions: `check.permission` on the controller constructor.

The `web` stack also appends `App\Http\Middleware\SetCurrentChannel` (`bootstrap/app.php`), which may assign `users.current_channel_id` and bind `currentChannel`.

Controller: `App\Http\Controllers\Admin\NewsletterSubscriberController`

| Method | URI | Name | Controller method | Group middleware | Permission |
| ------ | --- | ---- | ----------------- | ---------------- | ---------- |
| GET | `/admin/newsletters` | `admin.newsletters.index` | `index` | auth, verified.if-required, set.user.locale, role:Admin | `newsletter_view` |
| GET | `/admin/newsletters/create` | `admin.newsletters.create` | `create` | same | `newsletter_create` |
| POST | `/admin/newsletters` | `admin.newsletters.store` | `store` | same | `newsletter_create` |
| GET | `/admin/newsletters/edit/{id}` | `admin.newsletters.edit` | `edit` | same | `newsletter_edit` |
| PUT | `/admin/newsletters/{id}` | `admin.newsletters.update` | `update` | same | `newsletter_edit` |
| DELETE | `/admin/newsletters/{id}` | `admin.newsletters.delete` | `delete` | same | `newsletter_delete` |

Web CSRF applies to non-GET admin routes. DataTables loads `admin.newsletters.index` via AJAX GET with `newsletter_view`.

There is no PATCH or bulk-delete route.

### Sidebar

`resources/views/layouts/components/main-sidebar.blade.php`: parent Promotions block is `@canany(['promotion_view', 'catalog_rule_view', 'coupon_view', 'newsletter_view'])`. Link to `admin.newsletters.index` is `@can('newsletter_view')`. Label: `__('translations.admin.sidebar.newsletters')`.

---

## Named views

| View | Used by |
| ---- | ------- |
| `admin.newsletters.index` | `index` (HTML) |
| `admin.newsletters.create` | `create` |
| `admin.newsletters.edit` | `edit` |
| `admin.newsletters._form` | create + edit |
| `admin.newsletters.actions` | DataTables `actions` column |

Email view `emails.admin.contact-form` is used by `ContactFormMail`, not by these HTTP controllers directly.
