# Workflows

Page **publish** exists (`status` + `published_at`). There is **no** draft/preview API for unpublished pages. Widget `html` has `config.enable_preview` for the storefront HTML block only.

## Create and edit a page (admin)

1. Admin user must have a current channel (`SetCurrentChannel`). No channel → 403 on sync.
2. `page_create`: `GET /admin/pages/create` → `POST /admin/pages`.
3. Required: `title`, `status`. Slug optional (generated from title). Parent must be in the same channel.
4. After store, redirect to **edit** (`admin.pages.edit`).
5. Tabs: content (Quill → hidden `content`), navigation (header/footer flags, positions, `footer_section`, nav label, new tab), SEO, translations (per `channel_locale_codes()`), channel overrides for the current channel only.
6. `page_edit`: `PUT /admin/pages/{id}` stays on edit.

Soft-delete: `page_delete` on the index tree.

## Publish

1. Set `status` to `published`.
2. Optional `published_at` (datetime-local). Empty means “no scheduled gate”: `scopePublished` allows null **or** `<= now()`.
3. `draft` and `archived` never appear on:
   - `GET /api/v1/pages/{slugOrPath}`
   - menus (`headerMenu` / `footerMenu`)
   - `GET /api/v1/seo/page|pages/{slug}`
   - `/sitemaps/pages.xml`
4. Scheduling: status `published` with a **future** `published_at` is still hidden by `scopePublished`.
5. There is no token, `?preview=`, or admin-only GET for drafts.

Menu cache can remain stale up to 5 minutes after changing nav flags (`menu:{location}:channel:{id}:locale:{locale}` has no `updated_at` bust). Page JSON cache busts when `pages.updated_at` changes.

## Header / footer menus

1. On the page, enable `show_in_header` and/or `show_in_footer`.
2. Optional `header_position` / `footer_position` (else `pages.position`).
3. Optional `nav_label` (and per-locale `translations.*.nav_label`).
4. Footer column: pick `footer_section` from `setting('footer.sections')` (default labels Company, Customer Care, Legal, Social). Empty → menu inherits parent section or `"General"`.
5. Storefront: `GET /api/v1/menus` or `/menus/header` / `/menus/footer`. Nested `children` follow `parent_id` among pages that also pass the same menu scope.

Reordering the **page tree** (`POST /admin/pages/reorder`) changes sibling `position` (and thus menu order when header/footer positions are null). It does not change parent; drag is siblings-only in the UI.

## Attach widgets to a page (page builder)

1. Create widgets under `/admin/widgets` (`widget_create`) in the same channel.
2. On page edit (`page_edit`), choose a widget and optional `slot` (placeholder free text; empty → `content`).
3. `POST .../widgets/attach` — widget must share a channel with the page.
4. Drag rows to `POST .../widgets/reorder` (`ordering` = `page_widgets.id`).
5. Toggle `active` via `POST .../widgets/{pivotId}/toggle`.
6. Detach: `DELETE` (`page_delete`).

Each attach/toggle/reorder updates `pages.updated_at` so the **page** API cache key changes. Widget definition changes use `widgets.updated_at` on widget-level caches.

`starts_at` / `ends_at` / `visibility` are stored on `page_widgets` and enforced on the API, but the attach form does not set them.

## Home widgets

1. Widget `status = active`.
2. Leave `hide_on_home` unchecked to appear on `GET /api/v1/widgets/home`.
3. Order = widget `position` (`POST /admin/widgets/reorder` from the DataTables list).
4. Optional `?type[]=` and `?limit=`.
5. This list is **not** filtered by channel. Page-attached home content should use a published page (e.g. slug `home`) plus `GET /pages/home` if channel isolation is required.

## Widget types that resolve at read time

| Workflow | What the storefront gets |
|---|---|
| `random_products` | New `product_ids` every request (page cache does not freeze them) |
| `trending_products` | Ranked `product_ids` (15-minute ranking cache) |
| `promotions_all` | Live promotion cards; cache key includes promotions `updated_at` stamp |
| `promotions_selected` | Selected IDs, still `active()` + channel at read time |
| `contact_form` | `submit_endpoint` → `POST /api/v1/contact` |

Manual product/category/attribute widgets: save IDs in admin; storefront loads catalog separately.

## HTML widget preview flag

On type `html`, `config.enable_preview` is a checkbox stored in JSON. It is **not** a page preview and is not interpreted in PHP beyond being passed through `WidgetRenderer`.

## Translations and channel copy

- Empty translation payload (all fields blank) **deletes** that locale row.
- Empty channel override payload **deletes** the `page_channel_contents` row for that channel.
- Widget translation config is merged recursively over base `config` at resolve time.

## Images

Carousel slides and generic `config.images[]` upload to `storage/app/public/widgets` (disk `public`). API returns `asset('storage/...')` URLs.
