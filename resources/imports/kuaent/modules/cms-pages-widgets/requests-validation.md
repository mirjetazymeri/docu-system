# Requests and validation

## Page — `StorePageRequest` / `UpdatePageRequest`

`authorize()` true. `prepareForValidation` merges boolean `show_in_header`, `show_in_footer`, `nav_new_tab`.

| Field | Rules |
|---|---|
| `title` | required, string, max 255 |
| `slug` | nullable, string, max 255 (uniqueness is custom, per channel) |
| `content` | nullable string |
| `layout` | nullable string, max 100 |
| `status` | required, `draft` \| `published` \| `archived` |
| `meta_title` | nullable, max 255 |
| `meta_description` | nullable string |
| `published_at` | nullable date |
| `parent_id` | nullable, exists `pages.id`; update also `notIn` current id |
| `position` | nullable integer ≥ 0 |
| `show_in_header` / `show_in_footer` / `nav_new_tab` | nullable boolean |
| `header_position` / `footer_position` | nullable integer |
| `footer_section` | nullable string, max 100 |
| `nav_label` | nullable, max 255 |
| `channel_content` | nullable array; `*.title/content/meta_title/meta_description` nullable strings |
| `translations` | nullable array; each `locale` required_with translations, max 10; title/meta/nav max 255; content nullable |

**After hooks**

- Slug = `Str::slug(slug ?: title)`. If non-empty, must not exist on another page in the **current channel** (`forChannel`). Store: any page; update: exclude self.
- If `parent_id` set, parent must belong to the current channel.
- Update: parent must not be a descendant (`Page::descendants()`).

Controller still applies `$req->boolean(...)` for the three flags on store/update.

## Page widgets

### `AttachWidgetRequest`

- `widget_id` required, exists `widgets.id`
- `slot` nullable string, max 100

Controller additionally requires the widget in the current channel and overlapping channels with the page.

### `ReorderWidgetsRequest`

- `ordering` required array min 1
- `ordering.*` integer, exists `page_widgets.id`

Controller checks all IDs belong to that page; else 422 `Invalid widget ordering.`

### Inline (not FormRequest)

**Page reorder** (`PageController::reorder`): `parent_id` nullable exists pages; `ordering` required array of existing page ids. Channel/parent membership checked in the transaction.

**Toggle widget:** `$req->boolean('active')` — no FormRequest.

## Widget — `StoreWidgetRequest` / `UpdateWidgetRequest`

`UpdateWidgetRequest` extends `StoreWidgetRequest` with no extra rules.

FormRequest rules (first pass when the action type-hints the request):

| Field | Rules |
|---|---|
| `type` | required string max 50 (narrowed again in the controller) |
| `title` | nullable max 255 |
| `status` | required `active` \| `inactive` |
| `config` | nullable array |
| `config.video_url` / `config.small_device_link` | nullable url |
| `config.small_device_link_text` | nullable max 255 |
| `translations.*` | locale required_with, max 10; title/section_label max 255; html/css/js nullable strings; video urls; carousel slide text fields; contact_form copy fields (intro max 5000, labels max 100, etc.) |

`hide_on_home` is **not** in the FormRequest. The controller’s `validated()` adds:

- `type` `Rule::in(array_keys(getTypes()))`
- `hide_on_home` sometimes boolean
- `config` nullable array

Unchecked `hide_on_home` therefore becomes false via `?? false` after validation.

Type-specific numeric clamps happen in `buildConfig` (limits, overlay, interval), not as FormRequest rules.

## Widget reorder (inline)

`ordering` required array min 1; each id exists `widgets.id`. Must all belong to the current channel (422 otherwise).

## Public contact (used by `contact_form`)

`App\Http\Requests\Api\V1\ContactFormRequest`: `name` required max 100; `email` required rfc max 191; `subject` required max 255; `message` required 10–5000; `phone` nullable max 30.

Page/widget **API GET** endpoints have no FormRequests; query params are read loosely (`device`, `slot`, `include_widgets`, `page`, `type`, `limit`, `location`).
