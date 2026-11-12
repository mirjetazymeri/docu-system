# Controllers

## Admin `App\Http\Controllers\Admin\PageController`

Middleware: `page_view` (index), `page_create` (create/store), `page_edit` (edit/update/reorder/attach/reorderWidgets/toggle), `page_delete` (destroy/detach).

All reads/writes use `Page::forCurrentChannel()` except create (then `syncChannels` to the current channel only). Missing `current_channel_id` aborts 403.

| Method | Behavior |
|---|---|
| `index` | Tree: all channel pages grouped by `parent_id`, children attached in memory |
| `create` / `store` | Form extras: parent picker (exclude none), current channel. Store in a transaction: page, channels, channel_content, translations |
| `edit` / `update` | Loads pageWidgets.widget.channels, channelContents, translations, channels. Active widgets in the current channel for the attach dropdown |
| `destroy` | Soft-delete page |
| `reorder` | JSON `{ parent_id, ordering[] }`. Locks siblings in this parent **and** current channel; 422 if IDs are not that set; missing IDs appended after |
| `attachWidget` | Widget must be in current channel; page and widget channel sets must intersect; else `widget_id` error “Channel mismatch”. Creates `PageWidget` slot default `content`, position max+1, `active` true. Touches page |
| `detachWidget` | Deletes pivot by id; updates page `updated_at` |
| `reorderWidgets` | Two-pass position update (100000+i then i+1) to avoid unique `(page_id, widget_id, slot, position)` collisions |
| `toggleWidget` | `{ active: bool }` on pivot |

Boolean nav flags come from `$req->boolean(...)` in store/update (checkboxes).

`Route::resource('pages')` also registers `show`; this controller has **no** `show` method.

## Admin `App\Http\Controllers\Admin\WidgetController`

Middleware: `widget_view` (index), `widget_create` (create/store), `widget_edit` (edit/update/reorder), `widget_delete` (destroy).

`getTypes()` / `getLayouts()` are the admin palettes. `validated()` requires `type` ∈ those keys.

| Method | Behavior |
|---|---|
| `index` | DataTables JSON when `ajax`; else `admin.widgets.index` |
| `create` / `store` | Builds `config` via `buildConfig`; position = max in current channel + 1; sync channel + translations |
| `edit` / `update` | Same config builder; keeps existing images/slides when `keep_images` / `keep_image` |
| `destroy` | Detach pages, detach channels, soft-delete |
| `reorder` | `{ ordering: id[] }` for current channel. Full list replaces order; subset is spliced into the global position list so DataTables pagination drag does not scramble other pages |

`buildConfig` always writes common keys (`show_title`, `is_carousel`, `layout`, `section_label`, `images`, `images_links`, `images_alt`) plus type-specific keys. Uploads go to disk `public`, directory `widgets`.

`syncTranslations` writes title + type-specific config; empty locale rows are deleted.

## API `App\Http\Controllers\Api\V1\PageController`

Constructor: `WidgetRenderer`, `RandomProductWidgetResolver`.

`show(Request, string $slugOrPath)`:

- Locale from `app()->getLocale()`, channel from `app('currentChannel')`.
- Query: `device` (default `desktop`), `slot`, `include_widgets` (default true).
- Resolve published page in channel by numeric id or nested slug path.
- 5-minute cache (see [architecture.md](architecture.md)).
- Overlay translation then channel content; `PageResource`; attach widgets grouped by slot when `include_widgets`.
- Placement filter: active, window, widget channels (if any), `visibility.devices`.
- After cache: `refreshPagePayload` for `random_products`.

Response wrapper: `{ data: payload }`.

## API `App\Http\Controllers\Api\V1\WidgetController`

| Method | Route intent |
|---|---|
| `show` | Single widget by id. 404 if widget has channels and current channel is not among them. `random_products` uncached |
| `byPlacement` | `?page={slug}&slot=&device=`. Page lookup `forChannel` + slug, **not** `published()`. Does not filter widget channels |
| `home` | Active, `hide_on_home=false`, optional `type[]`, `limit` 1–200 (default 50). **No channel filter**. Ordered by widget `position`, `id` |

Each item includes `id`, `type`, `widget_key`, `title`, `config` (resolved). Placement rows also have `placement_id`, `slot`, `position`.

## API `App\Http\Controllers\Api\V1\MenuController`

- `index` — `{ data: { header, footer } }` or delegates to `show` when `?location=`.
- `show` — `header` \| `footer` only; else 404.
- `buildMenu` — `Page::headerMenu()` / `footerMenu()`, optional `forChannel`, nested by `parent_id`. Label: translation `nav_label` → page `nav_label` → translation `title` → page `title`. URL `'/' + path`. Footer items get `section`, `section_key`, `section_label` from `footer_section` or inherited parent or `'General'`. Footer tree sorted by section order then position.

Footer section definitions: `FooterSections::parse(setting('footer.sections', "Company\nCustomer Care\nLegal\nSocial"))`.

Cache key `menu:{location}:channel:{id}:locale:{locale}`, 5 minutes.

## Adjacent controllers (not CMS CRUD)

| Controller | CMS use |
|---|---|
| `Api\V1\SeoController` | `entity` `page` \| `pages` — same published path walk as page show |
| `SitemapController::pages` | `/sitemaps/pages.xml` — published pages in channel |
| `Api\V1\ContactController` | `POST /api/v1/contact` — `contact_form` `submit_endpoint` |
| `Admin\LookupController` | widget form lookups: products, categories, attributes, options, promotions |
| `Admin\SettingController` | `footer.sections` / column content (FooterSections) |

Theme `PagesController` / `WidgetsController` are **not** this module.
