# Controllers

Single controller: `App\Http\Controllers\Admin\MediaLibraryController`  
File: `app/Http/Controllers/Admin/MediaLibraryController.php`  
Extends `App\Http\Controllers\Controller`.

Constructor permission middleware is documented in [README.md](README.md) and [routes.md](routes.md).

Views: `admin.media.index`, `admin.media.create`, `admin.media.show`, `admin.media.edit`.

There is no API controller for this module (`routes/api.php` has no media routes).

---

## Folders

### `index()`

`MediaFolder::withCount('media')->orderBy('sort_order')->orderBy('name')->get()`.

Passes `$folders` to `admin.media.index`. The view uses `$folder->getFirstMedia('images')` and `$cover->getUrl('thumb')` for the card image, and `$folder->media_count` for the badge.

### `create()`

Returns `admin.media.create`.

### `store(Request $request)`

Validates folder fields (see [requests-validation.md](requests-validation.md)). Empty `slug` → `Str::slug($validated['name'])`. `MediaFolder::create($validated)`.

Redirect: `admin.media.show` with `$folder->slug`. Flash `success`: `Gallery folder "{name}" created.`

### `show(MediaFolder $mediaFolder)`

`$media = $mediaFolder->getMedia('images')`. View `admin.media.show` with `folder` and `media`.

### `edit(MediaFolder $mediaFolder)`

View `admin.media.edit` with `folder`.

### `update(Request $request, MediaFolder $mediaFolder)`

Same fields as store; slug unique rule ignores the current folder id. Empty slug → `Str::slug(name)`. `$mediaFolder->update($validated)`.

Redirect: `admin.media.show`. Flash `success`: `Folder updated.`

### `destroy(MediaFolder $mediaFolder)`

`$mediaFolder->clearMediaCollection('images')` then `$mediaFolder->delete()`.

Redirect: `admin.media.index`. Flash `success`: `Folder and all its images deleted.`

---

## Media items

### `upload(Request $request, MediaFolder $mediaFolder)`

`POST` intended for Dropzone (`paramName` `file` in `admin.media.show`). Comment in controller: one file per request.

1. Requires `file` as a file.
2. MIME `image/*`: Laravel `File::image()`, max 10 MB (`max(10 * 1024)` kilobytes), min dimensions 10×10.
3. MIME `video/*`: `File::types(['mp4', 'webm', 'mov', 'avi', 'ogv', 'mkv'])`, max 100 MB.
4. Other MIME: JSON `{ "message": "Unsupported file type. Allowed: images and videos." }` with HTTP 422.

Then:

```php
$media = $mediaFolder
    ->addMediaFromRequest('file')
    ->usingName($request->input('custom_name', pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)))
    ->withCustomProperties([
        'alt'     => $request->input('alt', ''),
        'caption' => $request->input('caption', ''),
    ])
    ->toMediaCollection('images');
```

The Dropzone script in `show.blade.php` does not send `custom_name`, `alt`, or `caption`. Those inputs are accepted if a client posts them.

JSON **201**:

| Key | Source |
|-----|--------|
| `id` | `$media->id` |
| `name` | `$media->name` |
| `file_name` | `$media->file_name` |
| `url` | `$media->getUrl()` |
| `thumb_url` | `null` if video; else `thumb` conversion URL if generated, otherwise original URL |
| `size` | `$media->size` |
| `mime_type` | `$media->mime_type` |
| `is_video` | `mime_type` starts with `video/` |
| `alt` | custom property `alt` |
| `caption` | custom property `caption` |

### `destroyMedia(Media $media)`

`$media->delete()`. If `request()->expectsJson()`: `{ "deleted": true }`. Otherwise `back()` with flash `success`: `Image deleted.`

The show-page JS sends `Accept: application/json` on DELETE. Bulk delete loops the same DELETE per id; there is no bulk endpoint.

### `updateMedia(Request $request, Media $media)`

Optional `name` (max 255), `alt` (max 255), `caption` (max 500).

If `name` is present in validated data, sets `$media->name`. Always `setCustomProperty('alt', …)` and `setCustomProperty('caption', …)` using the validated value or the existing custom property (default `''`). `$media->save()`.

JSON: `id`, `name`, `alt`, `caption`.

### `reorder(Request $request, MediaFolder $mediaFolder)`

Requires `ids` array of integers. Calls `Media::setNewOrder($request->input('ids'))`. The folder argument is unused in the method body.

JSON: `{ "reordered": true }`.

Show-page JS posts `{ ids: [ …dataset.id in grid order ] }` after drag-and-drop.

### `moveMedia(Request $request, Media $media)`

Requires `folder_slug` existing in `media_folders.slug`. Loads that folder, then `$media->move($targetFolder, 'images')`.

JSON: `{ "moved": true, "folder": $targetFolder->name }`.
