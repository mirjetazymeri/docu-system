# Media library

Admin gallery folders (`MediaFolder`) and Spatie Media Library uploads. Folders hold files in the Spatie collection `images` (images and videos). The admin UI is under `/admin/media`.

This module does **not** own product catalog images. Product files still live on `product_images` / `product_videos`; `Product` only registers Spatie collections (see [models.md](models.md)).

## Scope

| Area | Location |
|------|----------|
| Controller | `app/Http/Controllers/Admin/MediaLibraryController.php` |
| Model | `app/Models/MediaFolder.php` |
| Spatie media model | `Spatie\MediaLibrary\MediaCollections\Models\Media` (no app `Media` model) |
| Migrations | `database/migrations/2026_04_16_100000_create_media_folders_table.php`, `database/migrations/2026_04_16_101000_create_media_table.php` |
| Routes | `routes/web.php` (admin group) |
| Views | `resources/views/admin/media/{index,create,edit,show}.blade.php` |
| Permissions seeder | `database/seeders/ModulePermissionSeeder.php` (module name `Media`) |
| Lang | `lang/en/translations.php` and `lang/sq/translations.php` keys under `admin.media` |

There is no `config/media-library.php` in this app. Spatie loads its package config (see [dependencies.md](dependencies.md)).

There are no Form Request classes for this module. Validation is inline in the controller ([requests-validation.md](requests-validation.md)).

`tests/` contains no media-library coverage ([tests.md](tests.md)).

## Behaviour

1. Admin lists folders (`sort_order`, then `name`), with Spatie `media` counts.
2. Create/update folder: `name`, optional `slug` / `description` / `sort_order`. Empty slug becomes `Str::slug(name)` in the controller; the model also slugs on create/update.
3. Folder show loads `$folder->getMedia('images')`. Dropzone posts one file at a time to `admin.media.upload`.
4. Uploads go to collection `images` with custom properties `alt` and `caption`. Images get `thumb` and `medium` conversions (non-queued). Videos skip conversions.
5. AJAX updates name/alt/caption, deletes items, reorders via `Media::setNewOrder()`, and moves items to another folder with `$media->move($targetFolder, 'images')`.
6. Deleting a folder calls `clearMediaCollection('images')` then deletes the row.

Route model binding for folders uses `slug` (`MediaFolder::getRouteKeyName()`). Media items bind on Spatie `Media` id.

## Access

Route group: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`, prefix `admin`, name prefix `admin.`.

Controller middleware (`check.permission` → `App\Http\Middleware\AutoAuthorizePermission`):

| Permission | Actions |
|------------|---------|
| `media_view` | `index`, `show` |
| `media_create` | `create`, `store`, `upload` |
| `media_edit` | `edit`, `update`, `reorder`, `updateMedia`, `moveMedia` |
| `media_delete` | `destroy`, `destroyMedia` |

Blade uses `@can('media_*')` for the same names. Super Admin and access-level permissions are resolved in `AuthServiceProvider` / `AutoAuthorizePermission`.

`ModulePermissionSeeder` creates module `Media` and permissions `media_view`, `media_create`, `media_edit`, `media_delete` (`guard_name` `web`).

`resources/views/layouts/components/main-sidebar.blade.php` has **no** link to `admin.media.index`. There is no `translations.admin.sidebar` key for media. The UI is reachable by URL (`/admin/media`).

## HasMedia consumers

App models that `implement HasMedia` and `use InteractsWithMedia`:

| Model | Role |
|-------|------|
| `App\Models\MediaFolder` | This module (collection `images`) |
| `App\Models\Product` | Separate Spatie collections `product-images` and `product-gallery`; not used by `MediaLibraryController` |

`App\Models\Page` does **not** implement `HasMedia`. No other `app/Models` class implements `HasMedia`.

## Storage

`MediaFolder` does not set a disk on the collection. Spatie’s unpublished package default is `env('MEDIA_DISK', 'public')`. `.env.example` does not define `MEDIA_DISK`. Laravel `FILESYSTEM_DISK` in `.env.example` is `local` and is the framework default disk, not Spatie’s media disk.

Relevant disks: [dependencies.md](dependencies.md).

## Related docs

- [models.md](models.md)
- [database.md](database.md)
- [controllers.md](controllers.md)
- [routes.md](routes.md)
- [requests-validation.md](requests-validation.md)
- [dependencies.md](dependencies.md)
- [tests.md](tests.md)
