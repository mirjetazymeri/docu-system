# Routes

Defined only in `routes/web.php`. No media routes in `routes/api.php` or `routes/console.php`.

## Group

```php
Route::middleware(['auth', 'verified.if-required', 'set.user.locale', 'role:Admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () { /* … */ });
```

Aliases (`bootstrap/app.php`):

| Alias | Class |
|-------|--------|
| `check.permission` | `App\Http\Middleware\AutoAuthorizePermission` |
| `set.user.locale` | `App\Http\Middleware\SetUserLocale` |
| `role` | `App\Http\Middleware\RoleMiddleware` |
| `verified.if-required` | `App\Http\Middleware\EnsureEmailIsVerifiedIfRequired` |

The `web` group also appends `App\Http\Middleware\SetCurrentChannel`. CSRF applies (Dropzone / `fetch` send `X-CSRF-TOKEN`).

Controller extra middleware (per action): `check.permission:media_view|media_create|media_edit|media_delete` as listed below.

## Table

All URIs are under prefix `admin`. Named routes have prefix `admin.`. Parameter `{mediaFolder}` is `App\Models\MediaFolder` (slug). `{media}` is `Spatie\MediaLibrary\MediaCollections\Models\Media` (id).

| Method | URI | Name | Action | Permission |
|--------|-----|------|--------|------------|
| GET | `/admin/media` | `admin.media.index` | `index` | `media_view` |
| GET | `/admin/media/create` | `admin.media.create` | `create` | `media_create` |
| POST | `/admin/media` | `admin.media.store` | `store` | `media_create` |
| POST | `/admin/media/{mediaFolder}/upload` | `admin.media.upload` | `upload` | `media_create` |
| POST | `/admin/media/{mediaFolder}/reorder` | `admin.media.reorder` | `reorder` | `media_edit` |
| GET | `/admin/media/{mediaFolder}/edit` | `admin.media.edit` | `edit` | `media_edit` |
| PUT | `/admin/media/{mediaFolder}` | `admin.media.update` | `update` | `media_edit` |
| DELETE | `/admin/media/{mediaFolder}` | `admin.media.destroy` | `destroy` | `media_delete` |
| GET | `/admin/media/{mediaFolder}` | `admin.media.show` | `show` | `media_view` |
| PATCH | `/admin/media/items/{media}` | `admin.media.items.update` | `updateMedia` | `media_edit` |
| DELETE | `/admin/media/items/{media}` | `admin.media.items.destroy` | `destroyMedia` | `media_delete` |
| POST | `/admin/media/items/{media}/move` | `admin.media.items.move` | `moveMedia` | `media_edit` |

Registration order in `web.php`: `index` and `create` before `{mediaFolder}` routes; `{mediaFolder}/edit` before `{mediaFolder}` show; item routes (`media/items/…`) after the folder `{mediaFolder}` routes.

Show-page JS builds item URLs with `url('admin/media/items')` plus id (`PATCH`/`DELETE` `…/items/{id}`, `POST` `…/items/{id}/move`), not named routes.

## Binding

- Folders: `MediaFolder::getRouteKeyName()` is `slug`.
- Items: default Spatie `Media` key (`id`).
