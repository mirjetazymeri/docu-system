# Dependencies

## Composer (this app)

From `composer.json` `require`:

| Package | Constraint | Role for this module |
|---------|------------|----------------------|
| `php` | `^8.2` | Runtime |
| `laravel/framework` | `^12.0` | HTTP, filesystem, validation |
| `spatie/laravel-medialibrary` | `^11.21` | `HasMedia`, `InteractsWithMedia`, `Media`, conversions, `setNewOrder`, `move` |
| `spatie/laravel-permission` | `^6.21` | `HasRoles` on `User`; Gate continues to Spatie after access-level checks |

Locked in `composer.lock`: `spatie/laravel-medialibrary` **11.22.1**. That package requires `spatie/image` `^3.3.2` (lock: **3.9.4**), plus `ext-exif`, `ext-fileinfo`, `ext-json`.

`spatie/laravel-medialibrary` `suggest`:

- `league/flysystem-aws-s3-v3` — “Required to use AWS S3 file storage”
- `php-ffmpeg/php-ffmpeg` — video thumbnails
- `spatie/pdf-to-image` — PDF/SVG thumbnails

This app’s `composer.json` does **not** require those suggested packages. `MediaFolder` conversions skip non-`image/` MIME types, so videos are not converted to thumbs in this module.

Service provider (package extra): `Spatie\MediaLibrary\MediaLibraryServiceProvider`.

## Spatie config (not published)

There is no `config/media-library.php` under `config/`. Package defaults in `vendor/spatie/laravel-medialibrary/config/media-library.php` include:

| Key | Default in package file |
|-----|-------------------------|
| `disk_name` | `env('MEDIA_DISK', 'public')` |
| `conversions_disk_name` | `env('MEDIA_CONVERSIONS_DISK', null)` |
| `max_file_size` | `1024 * 1024 * 10` (10 MB) |
| `media_model` | `Spatie\MediaLibrary\MediaCollections\Models\Media` |
| `queue_conversions_by_default` | `env('QUEUE_CONVERSIONS_BY_DEFAULT', true)` |

`MEDIA_DISK`, `MEDIA_CONVERSIONS_DISK`, and `QUEUE_CONVERSIONS_BY_DEFAULT` are **not** in `.env.example`. Folder conversions call `nonQueued()`, so those conversions run in-process.

`MediaFolder` / `MediaLibraryController` do not call `toMediaCollectionOnDisk` or set a collection disk.

## Filesystem disks (`config/filesystems.php`)

Framework default disk: `env('FILESYSTEM_DISK', 'local')`. `.env.example` sets `FILESYSTEM_DISK=local`. That is Laravel’s default disk, not Spatie’s `disk_name`.

Disks defined:

### `local`

- driver `local`
- root `storage_path('app/private')`
- `serve` true; `throw` false; `report` false

### `public`

- driver `local`
- root `storage_path('app/public')`
- `url` `env('APP_URL').'/storage'`
- `visibility` `public`
- `throw` false; `report` false

Spatie package default `disk_name` is `public`. `links` maps `public_path('storage')` → `storage_path('app/public')` (Artisan `storage:link`).

### `s3`

Defined in the same file. Media-library PHP does not select this disk. Env **names** used by this disk config:

- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
- `AWS_BUCKET`
- `AWS_URL`
- `AWS_ENDPOINT`
- `AWS_USE_PATH_STYLE_ENDPOINT` (default `false` in config)

`.env.example` lists `AWS_ACCESS_KEY_ID`, `AWS_SECRET_ACCESS_KEY`, `AWS_DEFAULT_REGION`, `AWS_BUCKET`, `AWS_USE_PATH_STYLE_ENDPOINT`. It does not list `AWS_URL` or `AWS_ENDPOINT`.

## Front-end (CDN, not Composer)

`resources/views/admin/media/show.blade.php`:

- CSS: `https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css`
- JS: `https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js`

`Dropzone.autoDiscover = false`. Instance: `#mediaDropzone`, `paramName: 'file'`, `maxFilesize: 100`, CSRF header `X-CSRF-TOKEN`.

## Auth / permissions (app)

- Route: `role:Admin` (`App\Http\Middleware\RoleMiddleware`)
- Per-action: `check.permission:media_*` (`App\Http\Middleware\AutoAuthorizePermission`)
- Blade `@can('media_*')` and `auth()->user()?->can('media_delete')` via `App\Providers\AuthServiceProvider` `Gate::before` (Super Admin or `hasAccessLevelPermission`, else Spatie/other)
- Permissions seeded in `database/seeders/ModulePermissionSeeder.php`

## Not used by this module

- No Form Request classes
- No Horizon/queue jobs registered in app code for folder conversions (`nonQueued()`)
- `App\Http\Resources\Api\V1\PromotionResource` has a `media` array for promotion banner/icon/logo URLs; that is not Spatie Media Library / `media_folders`
