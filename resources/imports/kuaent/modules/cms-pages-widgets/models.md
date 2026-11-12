# Models

All under `app/Models/` unless noted.

## `Page`

- Soft deletes.
- Fillable: `slug`, `title`, `content`, `layout`, `status`, `meta_title`, `meta_description`, `published_at`, `parent_id`, `position`, `show_in_header`, `show_in_footer`, `nav_label`, `header_position`, `footer_position`, `footer_section`, `nav_new_tab`.
- Casts: `published_at` datetime; nav booleans.

**Relations**

| Method | Type |
|---|---|
| `parent()` | `belongsTo` Page |
| `children()` | `hasMany` Page, ordered by `position`, `id` |
| `pageWidgets()` | `hasMany` PageWidget, ordered by `position`, `id` |
| `widgets()` | `belongsToMany` Widget via `page_widgets`, using `PageWidget`, pivot fields including `slot`, `active`, `visibility`, window |
| `channels()` | `belongsToMany` Channel via `page_channel` |
| `translations()` | `hasMany` PageTranslation |
| `channelContents()` | `hasMany` PageChannelContent |
| `channelContent($channelId)` | `hasOne` PageChannelContent filtered by channel |

**Scopes**

- `roots` — `parent_id` null.
- `published` — `status = published` and (`published_at` null or `<= now()`).
- `headerMenu` / `footerMenu` — published + `show_in_header` / `show_in_footer`, order `COALESCE(header_position|footer_position, position), id`.
- `forChannel($channelId)` — `whereHas` channels.
- `forCurrentChannel()` — admin user’s `current_channel_id`.

**Helpers**

- `path` (accessor) — slug path from ancestors.
- `ancestors()` / `descendants()` — in-memory walks (descendants need `children` loaded to be cheap).
- `translation(?locale)` — requested locale, else `app.fallback_locale`.

**Hooks** (`booted`)

- Creating: slug from title/uuid if empty; position = max sibling + 1 if null.
- Saving: `Str::slug` on slug; cycle guard (cannot parent self or a descendant).

There is no `extra` column or fillable field (the unused seeder still sends `extra`).

## `PageTranslation`

Fillable: `page_id`, `locale`, `title`, `content`, `meta_title`, `meta_description`, `nav_label`.

Unique `(page_id, locale)` in the database. No `slug` column (unlike the unused seeder).

## `PageChannelContent`

Fillable: `page_id`, `channel_id`, `title`, `content`, `meta_title`, `meta_description`.

Unique `(page_id, channel_id)`.

`resolveContent(int $channelId)` is unused. It reads `$this->channelContents` / `$this->title` as if it were a `Page` instance; API overlay is done in `Api\V1\PageController` instead.

## `PageWidget` (`app/Models/PageWidget.php`)

Incrementing pivot (`table` `page_widgets`).

Fillable: `page_id`, `widget_id`, `slot`, `position`, `active`, `visibility`, `starts_at`, `ends_at`.

Casts: `visibility` array, window datetimes, `active` boolean.

`scopeActive` — `active` plus start/end window (API controllers re-implement the same checks in a `filter()` closure, including device visibility).

## `Widget`

- Soft deletes.
- Fillable: `type`, `title`, `config`, `status`, `position`, `hide_on_home`.
- Casts: `config` array, `hide_on_home` boolean.

**Relations:** `pages()` (same pivot as Page), `translations()`, `channels()` via `widget_channel`.

**Scopes:** `active` (`status = active`), `forChannel`, `forCurrentChannel`.

**Helpers**

- `translation(?locale)` — same locale/fallback pattern as Page.
- `localizedTitle()` — translation title or widget title.
- `localizedConfig()` — recursive merge of base config + translation config.

## `WidgetTranslation`

Fillable: `widget_id`, `locale`, `title`, `config` (JSON array). Unique `(widget_id, locale)`.

Translation `config` stores per-locale overrides: `section_label`; for `html` (`html`/`css`/`js`); for `video` (url/link/text); for `contact_form` (labels and copy); for `carousel` (per-slide text fields keyed by slide index).

## Support (not Eloquent)

`App\Support\FooterSections` — parse section labels, slug keys, contact-line helpers for settings. Menus and the page form use `parse()` only.
