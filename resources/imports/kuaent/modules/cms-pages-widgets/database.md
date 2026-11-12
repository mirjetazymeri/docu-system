# Database

## Current tables

### `pages`

Created in `2025_09_02_170723_create_pages_table.php.php`, then altered.

| Column | Notes |
|---|---|
| `id` | PK |
| `parent_id` | FK `pages.id`, nullable, `nullOnDelete` |
| `slug` | indexed (`pages_slug_index`); **not** globally unique |
| `title` | required at create |
| `content` | `longText`, nullable |
| `layout` | optional string (free text in admin) |
| `status` | default `draft` — app uses `draft` \| `published` \| `archived` |
| `position` | unsigned int, default 0 |
| `show_in_header` / `show_in_footer` | bool, default false |
| `nav_label` | nullable |
| `header_position` / `footer_position` | unsigned, nullable |
| `footer_section` | string 100, nullable |
| `nav_new_tab` | bool, default false |
| `meta_title` / `meta_description` | SEO |
| `published_at` | nullable timestamp |
| timestamps, `deleted_at` | soft deletes |

Constraints/indexes from migrations:

- Unique `(parent_id, slug)` among siblings (`add_parent_to_pages`). Root rows have `parent_id` null (MySQL unique allows multiple nulls).
- Global unique on `slug` was dropped in `2026_05_08_120000_drop_global_unique_slug_from_pages_table.php` so the **same slug can exist on different channels**. App validation still requires slug unique **per channel** (any parent).

### `page_translations`

`2026_04_13_120000_create_page_translations_table.php`

`page_id` FK cascade, `locale` (10), title/content/meta/nav_label, unique `(page_id, locale)`, index `locale`.

### `page_channel`

`2026_03_19_102544_create_page_channel_table.php`

`page_id`, `channel_id`, unique pair, both FKs cascade.

### `page_channel_contents`

`2026_03_19_102819_create_page_channel_contents_table.php`

Per-page per-channel title/content/meta. `content` is `text` (pages body is `longText`). Unique `(page_id, channel_id)`.

### `widgets`

`2025_09_02_170816_create_widgets_table.php` plus `position` and `hide_on_home`.

| Column | Notes |
|---|---|
| `type` | string; live values are `Admin\WidgetController::getTypes()` keys |
| `title` | admin label, nullable |
| `config` | JSON — all type-specific data |
| `status` | default `active` — app uses `active` \| `inactive` |
| `hide_on_home` | bool, default false |
| `position` | int, default 0 (home/admin list order) |
| timestamps, `deleted_at` | |
| index `(type, status)` | |

The original migration comment lists example types (`hero_slider`, `product_group`, …). Those are **not** the live admin types.

### `widget_translations`

`2026_04_13_130000_create_widget_translations_table.php`

`widget_id` FK cascade, `locale`, `title`, JSON `config`, unique `(widget_id, locale)`.

### `widget_channel`

`2026_03_19_121130_create_widget_channel_table.php`

`widget_id`, `channel_id`, unique pair, cascade.

### `page_widgets`

`2025_09_02_170849_create_page_widgets_table.php.php`

| Column | Notes |
|---|---|
| `page_id` / `widget_id` | FKs cascade |
| `slot` | string, default `content` (free text in admin) |
| `position` | unsigned, default 1 |
| `active` | bool, default true |
| `visibility` | JSON nullable — API reads `devices` only |
| `starts_at` / `ends_at` | nullable; filtered on API |
| timestamps | |
| unique `(page_id, widget_id, slot, position)` as `pw_unique` | same widget can appear twice if position/slot differ |

## Dropped tables

`2026_04_03_120000_drop_widget_link_tables.php` drops:

- `widget_products`
- `widget_categories`
- `widget_attributes`

Created earlier in `2025_09_02_170944_create_widget_links_tables.php.php`. Product/category/attribute picks now live in `widgets.config`.

## Migration file list (this module)

| File | Role |
|---|---|
| `2025_09_02_170723_create_pages_table.php.php` | pages |
| `2025_09_02_170816_create_widgets_table.php` | widgets |
| `2025_09_02_170849_create_page_widgets_table.php.php` | placements |
| `2025_09_02_170944_create_widget_links_tables.php.php` | later dropped |
| `2025_09_02_191008_add_position_to_widgets_table.php` | widget position |
| `2025_09_04_145603_add_parent_to_pages.php.php` | parent, position, sibling slug unique |
| `2025_09_07_150432_add_nav_fields_to_pages.php.php` | header/footer nav |
| `2025_09_07_205345_add_hide_on_home_to_widgets_table.php.php` | home flag |
| `2026_03_19_102544_create_page_channel_table.php` | page ↔ channel |
| `2026_03_19_102819_create_page_channel_contents_table.php` | channel copy |
| `2026_03_19_121130_create_widget_channel_table.php` | widget ↔ channel |
| `2026_04_03_120000_drop_widget_link_tables.php` | drop link tables |
| `2026_04_09_120000_add_footer_section_to_pages_table.php` | footer column key |
| `2026_04_13_120000_create_page_translations_table.php` | page i18n |
| `2026_04_13_130000_create_widget_translations_table.php` | widget i18n |
| `2026_05_08_120000_drop_global_unique_slug_from_pages_table.php` | slug per channel |

## Seeders

`database/seeders/PageSeeder.php` exists but is **not** invoked from `DatabaseSeeder`. It is stale:

- Creates pages without `slug`/`title` fill that the current model expects from admin (creating hook can invent a slug).
- Writes `extra` (not a column/fillable).
- Writes `PageTranslation.slug` (column does not exist).
- Instantiates `PageSection` / `PageSectionTranslation` (no models or tables in this codebase).
- Nested widget JSON uses `hero_slider`, `product_grid`, `category_carousel` — **not** live `widgets.type` values.

Permissions for this module are seeded as modules `Pages` and `Widgets` in `ModulePermissionSeeder` (`page_*` / `widget_*` actions).
