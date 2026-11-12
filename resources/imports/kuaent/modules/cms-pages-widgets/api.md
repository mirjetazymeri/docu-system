# Public API

All routes are under `/api/v1` with `channel.api` and `locale.api`. Responses wrap payloads in `{ "data": ... }` unless noted.

Channel resolution (`ResolveApiChannel`): query `channel` (channel **code**), header `X-Channel`, authenticated user’s current channel, else channel code `default`.

## `GET /pages/{slugOrPath}`

**Auth:** none. **Published pages only**, scoped to current channel.

| Query | Default | Effect |
|---|---|---|
| `device` | `desktop` | Compared to `page_widgets.visibility.devices` when that array is set |
| `slot` | omitted | Restrict placements to one slot |
| `include_widgets` | `1` (true) | If false, page fields only |

`slugOrPath` may be a numeric page id or a nested path (`about/team`). Each path segment is a `slug` under the previous page as parent.

### Page object (`PageResource` + optional widgets)

| Field | Source |
|---|---|
| `id`, `slug`, `path`, `layout`, `status`, `published_at` | page |
| `title` | translation title, else page title (see overlay note in [architecture.md](architecture.md)) |
| `content` | HTML string after controller overlay |
| `content_format` | always `"html"` |
| `meta.title` / `meta.description` | translation then page |
| `parent_id` | |
| `breadcrumbs` | ancestors: `id`, `slug`, `title`, `path` |
| `children` | direct children: `id`, `slug`, `title`, `path` |
| `widgets` | omitted when `include_widgets` is false; else map **slot → array of widgets** |

### Widget item on a page

```
placement_id, id, type, widget_key, title, slot, position, config
```

`config` is `WidgetRenderer::resolve` output (localized, URLs expanded, type appenders). `random_products.product_ids` is re-picked after cache.

404 if any path segment is missing or unpublished / wrong channel.

## `GET /widgets/{id}`

Numeric id only. 404 if the widget has channels and the current channel is not attached.

Does **not** require `status=active`.

`random_products` is not cached. Other types cache 5 minutes with widget `updated_at` and, for `promotions_all`, the promotions stamp.

Shape: `{ id, type, widget_key, title, config }`.

## `GET /widgets`

Placement list.

| Query | Effect |
|---|---|
| `page` | Page `slug` in current channel (`firstOrFail`). **Not** filtered by published |
| `slot` | Filter `page_widgets.slot` |
| `device` | default `desktop` |

If `page` is omitted, all `page_widgets` rows are loaded (then filtered by active/window/device). Widget **channels are not filtered**.

`data` is a map of slot → widget arrays (`placement_id`, `id`, `type`, `widget_key`, `title`, `slot`, `position`, `config`).

## `GET /widgets/home`

Home rail. **Not** channel-scoped.

| Query | Effect |
|---|---|
| `type` | Optional; `array_filter` of types (`type[]` or repeated) |
| `limit` | default 50, cap 200 |

Filters: `status=active`, `hide_on_home=false`. Order: widget `position`, `id`.

Shape: array of `{ id, type, widget_key, title, config }`.

## `GET /menus` and `GET /menus/{location}`

`location` must be `header` or `footer` (404 otherwise). `GET /menus?location=` delegates to `show`.

Each node:

| Field | Meaning |
|---|---|
| `id` | page id |
| `label` | nav_label (translated) → nav_label → title |
| `url` | `'/' + path` |
| `target` | `_blank` if `nav_new_tab`, else `_self` |
| `section` | footer only; page `footer_section`, else inherited, else `General` |
| `section_key` | slug of section (`_` separator) |
| `section_label` | label from `footer.sections` when the key matches |
| `children` | nested menu nodes |
| `position` | header_position or footer_position, else page position |

Only **published** pages with the matching show-in-header/footer flag, in the current channel when `channelId > 0`.

Footer list is sorted by section order (from settings) then position.

## `GET /seo/page/{slug}` / `GET /seo/pages/{slug}`

Same published path (or numeric id) as page show. Returns meta/open_graph/twitter. Canonical is channel `domain` (https prefixed if needed) + `/{path}`, else `config('app.url')`.

## Resolved `config` additions by type

| type | Extra on `config` at read time |
|---|---|
| `promotions_all` / `promotions_selected` | `promotions` array (`PromotionResource`) |
| `random_products` / `trending_products` | `product_ids` (integers) |
| `contact_form` | `submit_endpoint` absolute URL to `/api/v1/contact` |
| `carousel` | `slides[].image` as full storage URL |
| any with `images` | `images` as `{ url, alt }[]` |

Product widgets that only store IDs do not embed product documents. The storefront loads them via catalog projection APIs.

## Contact submit (widget consumer)

`POST /api/v1/contact` — see [requests-validation.md](requests-validation.md). Success: `{ success: true, message: "..." }`. Phone is accepted by the request but **not** passed to `MailService::forwardContactForm`.
