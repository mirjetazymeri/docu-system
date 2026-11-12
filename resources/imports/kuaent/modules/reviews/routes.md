# Reviews — Routes

Module README: [README.md](README.md) · API payloads: [api.md](api.md)

Laravel loads `routes/api.php` via `bootstrap/app.php` `withRouting(api: ...)`, which prefixes URIs with `/api`. Web admin routes are in `routes/web.php`.

## API (`routes/api.php`)

Group:

```text
Route::prefix('v1')->middleware(['channel.api', 'locale.api'])
```

Review routes are **not** inside `Route::middleware('auth:sanctum')`. No per-route throttle is attached (unlike e.g. `contact` on the same file).

`channel.api` → `App\Http\Middleware\ResolveApiChannel`. `locale.api` → `App\Http\Middleware\SetLocaleFromRequest`. Product resolution uses `app()->getLocale()`; it does not read `channel_id`.

Controller: `App\Http\Controllers\Api\V1\ProductReviewController`

| Method | URI | Name | Controller | Middleware | Permission |
| ------ | --- | ---- | ---------- | ---------- | ---------- |
| POST | `/api/v1/products/{idOrSku}/reviews` | *(unnamed)* | `store` | `channel.api`, `locale.api` | none |
| PUT | `/api/v1/reviews/{id}` | *(unnamed)* | `update` | same | policy or guest token |
| PATCH | `/api/v1/reviews/{id}` | *(unnamed)* | `update` | same | policy or guest token |
| DELETE | `/api/v1/reviews/{id}` | *(unnamed)* | `destroy` | same | policy or guest token |

There is no `GET` reviews index or show route.

---

## Web admin (`routes/web.php`)

Group:

```text
Route::middleware(['auth', 'verified.if-required', 'set.user.locale', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
```

`role:Admin` is `App\Http\Middleware\RoleMiddleware` with Spatie `hasAnyRole('Admin')`. Additional permissions: `check.permission` on the controller constructor.

Controller: `App\Http\Controllers\Admin\ProductReviewController`

| Method | URI | Name | Controller | Group middleware | Permission |
| ------ | --- | ---- | ---------- | ---------------- | ---------- |
| GET | `/admin/reviews` | `admin.reviews.index` | `index` | auth, verified.if-required, set.user.locale, role:Admin | `review_view` |
| GET | `/admin/reviews/edit/{id}` | `admin.reviews.edit` | `edit` | same | `review_edit` |
| PUT | `/admin/reviews/{id}` | `admin.reviews.update` | `update` | same | `review_edit` |
| POST | `/admin/reviews/{id}/status` | `admin.reviews.status` | `setStatus` | same | `review_edit` |
| POST | `/admin/reviews/bulk` | `admin.reviews.bulk` | `bulk` | same | `review_edit` |
| DELETE | `/admin/reviews/{id}` | `admin.reviews.delete` | `delete` | same | `review_delete` |

Web CSRF applies to non-GET admin routes (standard `web` stack). DataTables loads `admin.reviews.index` via AJAX GET with the same `review_view` gate.

Sidebar: `resources/views/layouts/components/main-sidebar.blade.php` — `@can('review_view')` link to `admin.reviews.index`.

---

## Named views

| View | Used by |
| ---- | ------- |
| `admin.reviews.index` | `index` (HTML) |
| `admin.reviews.edit` | `edit` |
| `admin.reviews.actions` | DataTables `actions` column |
