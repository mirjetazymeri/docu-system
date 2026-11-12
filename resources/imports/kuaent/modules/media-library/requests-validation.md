# Requests and validation

There are **no** `app/Http/Requests` classes for media folders or media items. `MediaLibraryController` uses `$request->validate()` (and a second validate after MIME sniffing on upload).

Create/edit Blade forms use `@error` / `old()` for folder fields. Upload/update/reorder/move are JSON from Dropzone/`fetch`; validation failures are Laravel’s default JSON 422 for those requests.

---

## `store` / `update` (folder)

| Field | Rules (`store`) | Rules (`update`) |
|-------|-----------------|------------------|
| `name` | required, string, max 120 | same |
| `slug` | nullable, string, max 120, `unique:media_folders,slug` | nullable, string, max 120, `unique:media_folders,slug,{id}` |
| `description` | nullable, string, max 500 | same |
| `sort_order` | nullable, integer, min 0 | same |

After validation, empty `slug` is replaced with `Str::slug($validated['name'])` in the controller. The model also slugs empty/dirty-name cases (see [models.md](models.md)).

Create form fields: `name`, `slug`, `description`, `sort_order` (`resources/views/admin/media/create.blade.php`). Edit form: same plus `_method` PUT (`edit.blade.php`).

---

## `upload`

First pass: `file` required, `file`.

Then by `$file->getMimeType()`:

**`image/`**

- `Illuminate\Validation\Rules\File::image()`
- `max(10 * 1024)` (10 MB; Laravel file `max` is kilobytes)
- `dimensions`: min width 10, min height 10 (`Rule::dimensions()->minWidth(10)->minHeight(10)`)

**`video/`**

- `File::types(['mp4', 'webm', 'mov', 'avi', 'ogv', 'mkv'])`
- `max(100 * 1024)` (100 MB)

**else**

HTTP 422 JSON: `{ "message": "Unsupported file type. Allowed: images and videos." }` (not the validator).

Unvalidated optional inputs used if present: `custom_name`, `alt`, `caption`.

Collection MIME allow-list is also on `MediaFolder::registerMediaCollections()` ([models.md](models.md)). Dropzone `acceptedFiles` in `show.blade.php`: `image/*,video/mp4,video/webm,video/quicktime,video/x-msvideo,video/ogg,video/x-matroska`. Dropzone `maxFilesize` is `100` (MB). UI copy (`translations.admin.media.upload_formats`): JPG, PNG, GIF, WEBP, SVG, MP4, WEBM, MOV, AVI, OGV, MKV; images 10 MB / videos 100 MB.

Spatie package config (unpublished) sets `'max_file_size' => 1024 * 1024 * 10` (10 MB). That is the package default loaded when `config/media-library.php` is not published.

---

## `updateMedia`

| Field | Rules |
|-------|--------|
| `name` | nullable, string, max 255 |
| `alt` | nullable, string, max 255 |
| `caption` | nullable, string, max 500 |

Show-page JS PATCHes JSON `{ name, alt, caption }`.

---

## `reorder`

| Field | Rules |
|-------|--------|
| `ids` | required, array |
| `ids.*` | integer |

Show-page JS POSTs JSON `{ ids: [...] }` (string ids from `data-id` are still validated as integer).

---

## `moveMedia`

| Field | Rules |
|-------|--------|
| `folder_slug` | required, string, `exists:media_folders,slug` |

Show-page JS POSTs JSON `{ folder_slug }`.
