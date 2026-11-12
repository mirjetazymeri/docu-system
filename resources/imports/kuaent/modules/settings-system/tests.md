# Settings & system tests

There are **no** Feature tests for `SettingController`, `Api\V1\SettingController`, `AdminThemeController`, or `SystemCacheController`. There are **no** unit tests for `SettingService`, `SystemCacheService`, `FooterSections`, or `SettingResource`.

## `AdminThemeAttributesTest`

**File:** `tests/Unit/AdminThemeAttributesTest.php`

PHPUnit `Tests\TestCase` (Laravel application). `setUp` instantiates `new AdminThemeService()` (not the container singleton).

Exercises `attributesFor()`, `hexToRgb()`, and `htmlAttributeString()`. Does **not** hit `admin_theme_settings` or the cache (empty theme arrays + config defaults). `htmlAttributeString()` reads live `all()` (defaults merged with any stored row in the test DB).

| Method | Asserts |
|--------|---------|
| `test_defaults_match_current_panel` | Empty theme → `dir=ltr`, `data-theme-managed=1`, vertical/light/transparent/flat/fullwidth, `data-vertical-style=default`, `loader=disable`; **no** `data-toggled`, `data-nav-style`, `data-bg-img` |
| `test_vertical_sidemenu_styles_are_wired_without_pre_toggled` | Data provider `verticalStyles`: `default`, `closed`, `icontext`, `overlay`, `detached`, `doublemenu` → `data-vertical-style` equals style; no `data-nav-style` / `data-toggled` |
| `test_vertical_click_hover_styles_replace_sidemenu_layout` | Provider `navStyles`: `menu-click`, `menu-hover`, `icon-click`, `icon-hover` → `data-nav-style` set; **no** `data-vertical-style` |
| `test_horizontal_layout_uses_nav_style` | Same nav styles on horizontal; no `data-vertical-style` |
| `test_horizontal_defaults_to_menu_click` | Horizontal + empty `nav_style` → `menu-click` |
| `test_theme_mode` | `light`, `dark` → `data-theme-mode` |
| `test_direction` | `ltr`, `rtl` → `dir` |
| `test_page_styles` | `regular`, `classic`, `modern`, `flat` → `data-page-style` |
| `test_layout_widths` | `default`, `fullwidth`, `boxed` → `data-width` |
| `test_menu_and_header_colors` | `light`, `dark`, `color`, `gradient`, `transparent` |
| `test_menu_and_header_positions` | `fixed`, `scrollable` |
| `test_loader` | `enable`, `disable` |
| `test_menu_background_images` | `bgimg1`–`bgimg5` → `data-bg-img` |
| `test_empty_menu_image_is_omitted` | empty `menu_bg_image` → no `data-bg-img` |
| `test_primary_color_becomes_css_rgb` | `#097c67` → `9, 124, 103`; `#2a10a4` → `42, 16, 164`; `#ff49cd` → `255, 73, 205`; `''` → `null` |
| `test_html_attribute_string_is_safe` | string contains `data-theme-managed="1"` and `data-nav-layout="vertical"`; does not contain `data-toggled=` |

Run:

```bash
php artisan test tests/Unit/AdminThemeAttributesTest.php
```

`phpunit.xml` sets `CACHE_STORE=array` for the suite (not specific to this class).

## Related (not this module’s tests)

`tests/Unit/CheckoutAddressFieldsTest.php` unbinds `SettingService` and asserts `config/settings.php` **defaults** for `checkout.address_fields.*` and `checkout.guest_enabled`. Owned by checkout field support, not settings controllers. See [cart-checkout](../cart-checkout/README.md).
