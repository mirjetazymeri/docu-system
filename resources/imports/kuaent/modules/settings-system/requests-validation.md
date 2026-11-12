# Settings & system validation

There are **no** `App\Http\Requests` classes for settings, admin theme, or system cache. Rules are inline `$request->validate()` on the controllers. `authorize()` is not used; access control is middleware.

System-cache POSTs have **no** request body validation (CSRF + permission only).

## Admin settings — `SettingController::buildValidation`

Built per flattened `config/settings.php` field. Always starts with `required` or `nullable` from `!empty($field['required'])`.

| Schema `type` | Additional rules |
|---------------|------------------|
| `text` | `string`, `max:1000` |
| `color` | `string`, `regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/` |
| `file` | `sometimes`, `file`, `mimes:jpg,jpeg,png,gif,webp,svg,ico`, `max:2048` (KB) |
| `number` | `numeric` (no min/max) |
| `email` | `string`, `email`, `max:255` |
| `textarea` | `string`, `max:5000` |
| `boolean` | `boolean` |
| `password` | `string`, `max:1000` |
| `select` | `string`; if `options` non-empty, `in:` comma-joined option values |
| `footer_column_content` | `nullable`, `array` (then replaced — see below) |
| other / missing type | required/nullable only |

`update()` then **unsets** `footer.column_content` from that map and applies:

| Field | Rules |
|-------|--------|
| `footer.column_content` | `nullable`, `array` |
| `footer.column_content.*` | `nullable`, `array` |
| `footer.column_content.*.text` | `nullable`, `string`, `max:5000` |
| `footer.column_content.*.items` | `nullable`, `array` |
| `footer.column_content.*.items.*.label` | `nullable`, `string`, `max:255` |
| `footer.column_content.*.items.*.type` | `nullable`, `string`, `in:` `FooterSections::contactLineTypeKeys()` |
| `footer.column_content.*.items.*.value` | `nullable`, `string`, `max:255` |
| `footer.column_content.*.items.*.url` | `nullable`, `string`, `max:1000` |
| `footer.column_content.*.items.*.image` | `nullable`, `string`, `max:255` |

After Laravel validate: `FooterSections::validateColumnContentInput(...)`. If a contact row is “present” (any of type/label/value/url/image non-empty) and `type` is empty or not in the allow-list, throws `ValidationException` on `footer.column_content.{Section}.items.{i}.type` — message `Type is required for each contact line.`

Boolean fields: Blade posts hidden `0` plus checkbox `1`. PHP treats absence as `false` even if validation never saw the key.

File fields: `hasFile($key)` uses **dot** keys (`general.logo`), not bracket names. `sometimes` avoids failing an empty file input.

`tax.shipping.tax_class_code`: schema type `text` unless `hydrateDynamicSchema()` found tax class codes, then `select` + `in:` those codes.

## Admin theme — `AdminThemeController::update`

| Field | Rules |
|-------|--------|
| `primary_color` | `required`, `string`, `regex:/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/` |
| `secondary_color` | same as primary |
| `background_color` | `nullable`, `string`, `regex:/^$|^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/` (empty string allowed) |
| `use_custom_background` | `nullable`, `boolean` |
| each key in `config('admin_theme.options')` | `nullable`, `string`, `Rule::in(array_keys(config('admin_theme.options.'.$key)))` |

Option keys at time of writing: `theme_mode`, `direction`, `nav_layout`, `nav_style`, `vertical_style`, `page_style`, `width`, `menu_styles`, `header_styles`, `menu_position`, `header_position`, `loader`, `menu_bg_image`.

`nav_style` allow-list includes `''` (None). `menu_bg_image` includes `''` plus `bgimg1`–`bgimg5`.

Post-validate mutations (not validation): clear background unless `use_custom_background`; horizontal empty nav style → `menu-click`; vertical with a click/hover nav style forces `vertical_style` to existing or `overlay`.

## Test email

`template` is **not** in a FormRequest. Unknown values `abort(400, 'Unknown template')`. Allowed: `welcome`, `verify-email`, `login-alert` (default `welcome`).

## Public API

`GET /api/v1/settings` has no query validation. Channel failure is 400 from the controller, not a validator.
