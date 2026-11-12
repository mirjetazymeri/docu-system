# Database

Two migrations own this module’s tables. No later migrations in `database/migrations` alter `media` or `media_folders`.

## `media_folders`

File: `database/migrations/2026_04_16_100000_create_media_folders_table.php`.

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `name` | string | |
| `slug` | string | unique |
| `description` | text | nullable |
| `sort_order` | integer | default `0` |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

`down()`: `dropIfExists('media_folders')`.

No foreign keys. Folder files are not stored as FK rows here; they are Spatie `media` rows with `model_type` / `model_id` pointing at this table.

## `media` (Spatie)

File: `database/migrations/2026_04_16_101000_create_media_table.php`.

Matches Spatie Media Library v11’s `create_media_table` stub (`vendor/spatie/laravel-medialibrary/database/migrations/create_media_table.php.stub`).

| Column | Type | Notes |
|--------|------|--------|
| `id` | bigint PK | |
| `model_type` | string | part of `morphs('model')` |
| `model_id` | unsigned bigint | part of `morphs('model')`; composite index with `model_type` |
| `uuid` | uuid | nullable, unique |
| `collection_name` | string | folder uploads use `images` |
| `name` | string | display name (`usingName` / `updateMedia`) |
| `file_name` | string | |
| `mime_type` | string | nullable |
| `disk` | string | filesystem disk name stored per row |
| `conversions_disk` | string | nullable |
| `size` | unsigned bigint | bytes |
| `manipulations` | json | |
| `custom_properties` | json | folder UI stores `alt`, `caption` |
| `generated_conversions` | json | |
| `responsive_images` | json | |
| `order_column` | unsigned integer | nullable, indexed; used by `Media::setNewOrder()` |
| `created_at` | timestamp | `nullableTimestamps()` |
| `updated_at` | timestamp | `nullableTimestamps()` |

`down()`: `dropIfExists('media')`.

The app does not register a morph map (`Relation::morphMap` / `enforceMorphMap` not used in `app/`). `model_type` is the FQCN (`App\Models\MediaFolder` or `App\Models\Product`).

## Seeders / factories

- `database/seeders/ModulePermissionSeeder.php` seeds module `Media` and permissions `media_view`, `media_create`, `media_edit`, `media_delete`. It does not insert folders or media rows.
- No `MediaFolder` factory under `database/factories`.
- `database/seeders/PageSeeder.php` contains widget slide paths like `/media/slide1.jpg`. Those are CMS slide URLs, not `media_folders` / Spatie `media` rows.
