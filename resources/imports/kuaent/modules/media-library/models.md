# Models

## `App\Models\MediaFolder`

File: `app/Models/MediaFolder.php`.

Table: `media_folders`. Extends `Illuminate\Database\Eloquent\Model`. No `SoftDeletes`. No factory.

```php
class MediaFolder extends Model implements HasMedia
{
    use InteractsWithMedia;
}
```

### Fillable

`name`, `slug`, `description`, `sort_order`.

No `$casts` on the model. `sort_order` is an integer column with default `0` (see [database.md](database.md)).

### Route key

`getRouteKeyName()` returns `slug`. Admin routes `{mediaFolder}` resolve by slug.

### Booted slug rules

On **creating**, if `slug` is empty: `Str::slug($folder->name)`.

On **updating**, if `name` is dirty and `slug` is not dirty: `Str::slug($folder->name)`.

The controller also fills an empty slug with `Str::slug($validated['name'])` before `create` / `update`.

### Spatie collection `images`

`registerMediaCollections()`:

```php
$this->addMediaCollection('images')
    ->acceptsMimeTypes([
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'image/bmp',
        'video/mp4',
        'video/webm',
        'video/quicktime',
        'video/x-msvideo',
        'video/ogg',
        'video/x-matroska',
    ]);
```

No disk is set on the collection.

### Conversions

`registerMediaConversions(?Media $media = null)`:

- If `$media` is not null and `mime_type` does not start with `image/`, the method returns (no conversions for that file).
- `thumb`: width 300, height 300, sharpen 10, `nonQueued()`.
- `medium`: width 900, height 900, sharpen 5, `nonQueued()`.

### Relationships used by this module

Spatie `InteractsWithMedia` provides `media()` (morphMany to the Spatie `Media` model). The index action uses `MediaFolder::withCount('media')`. Folder UI uses `getMedia('images')`, `getFirstMedia('images')`, `clearMediaCollection('images')`, `addMediaFromRequest('file')->…->toMediaCollection('images')`.

Custom properties written on upload / update: `alt`, `caption`.

---

## Spatie `Media` (not an app model)

Class: `Spatie\MediaLibrary\MediaCollections\Models\Media`.

Used by `MediaLibraryController` for `destroyMedia`, `updateMedia`, `moveMedia`, and `Media::setNewOrder()` in `reorder`.

Package default `media_model` is this class (`vendor/spatie/laravel-medialibrary/config/media-library.php`). The app does not publish that config or define `App\Models\Media`.

Table: `media` (polymorphic `model_type` / `model_id`). Folder files have `model_type` for `App\Models\MediaFolder` and `collection_name` `images`.

---

## Other `HasMedia` consumers (not this module’s models)

Do not treat these as media-library domain models. Listed because they share the `media` table.

### `App\Models\Product`

Implements `HasMedia`, uses `InteractsWithMedia` (with `SoftDeletes`).

Collections registered on the product model:

| Collection | `acceptsMimeTypes` |
|------------|-------------------|
| `product-images` | `image/jpeg`, `image/png`, `image/gif`, `image/webp` |
| `product-gallery` | same |

Conversions on `Product`: `thumb` 300×300 sharpen 10; `medium` 800×800 sharpen 5; `large` 1200×1200 sharpen 3; all `nonQueued()`.

`Product` also has Eloquent `images()` → `ProductImage` and `videos()` → `ProductVideo`. `MediaLibraryController` does not attach media to products. A search of `app/` found no `addMedia` / `toMediaCollection` calls on `Product`.

### `App\Models\Page`

Does **not** implement `HasMedia` and does not use `InteractsWithMedia`.

No other class under `app/Models` implements `HasMedia`.
