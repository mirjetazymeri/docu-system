# Settings & system

Channel-scoped **app settings**, **Laravel/API cache maintenance**, **storefront ISR revalidation** (via [external-catalog](../external-catalog/README.md)), and the **admin-panel theme** (Vyzor HTML attributes + CSS variables).

This folder documents implemented PHP in `C:\laragon\www\TestDocu-admin` only. It is not a Composer package.

## What this module owns

- Schema-driven settings UI and persistence (`config/settings.php`, `Setting`, `SettingService`, admin `SettingController`)
- Public storefront settings API (`GET /api/v1/settings`, `SettingResource`)
- Helpers `setting()`, `app_name()`, `setting_file_url()`, `admin_theme()` in `app/helpers.php`
- Footer column JSON helpers (`App\Support\FooterSections`) used on save and on the public API
- Admin cache/optimize/revalidate UI (`SystemCacheController`, `SystemCacheService`)
- Admin chrome theme (`config/admin_theme.php`, `AdminThemeSetting`, `AdminThemeService`, `AdminThemeController`)

## What this module does not own

- Tax class/rate CRUD — [tax](../tax/README.md) (this module only stores the `tax.*` flags and hydrates `tax.shipping.tax_class_code` options)
- Channel/locale records and `currentChannel` binding — [channels-locales](../channels-locales/README.md)
- Permission catalog and access-level enforcement — [users-access-control](../users-access-control/README.md)
- Mail dispatch (`MailService`) — [shared mail](../../shared/README.md#mail); Fortify/Sanctum flows — [auth](../auth/README.md); SMTP **driver/host/password** fields are stored here but are **not** applied to Laravel’s mailer in `app/`
- Catalog search/facets/projections — [catalog-products](../catalog-products/README.md) (`CatalogSearchSettings`, `CatalogFacetSettings` only **read** keys)
- External order export and `StorefrontRevalidator` implementation — [external-catalog](../external-catalog/README.md)
- Consent/event ingest and `gdpr:purge-telemetry` — [gdpr-storefront-telemetry](../gdpr-storefront-telemetry/README.md)
- CMS page/widget CRUD — [cms-pages-widgets](../cms-pages-widgets/README.md) (footer **section labels** are settings; page `footer_section` assignment is CMS)
- `Admin\LookupController` (products/categories/attributes/promotions/shipments) — not settings-related
- Artisan `storefront:verify-revalidate` — not invoked by this module (see [dependencies.md](dependencies.md))

## Admin entry

Sidebar **System configs** (`resources/views/layouts/components/main-sidebar.blade.php`):

| Item | Permission | Route |
|------|------------|--------|
| All configs | `setting_view` | `admin.settings.index` → `/admin/settings` |
| Admin Theme | `theme_view` | `admin.theme.edit` → `/admin/theme` |
| Cache / revalidate | `system_cache_view` | `admin.system_cache.index` → `/admin/system-cache` |

Settings save and test-email require `setting_edit`. Theme save/reset require `theme_edit`. Cache actions require `system_cache_edit`.

Settings rows are **per admin current channel** (`users.current_channel_id`). Admin theme is **global** (single `admin_theme_settings` row, not channel-scoped).

## Runtime flow (short)

1. Admin opens Settings; `SettingService::all()` loads cached `settings` rows for the resolved channel; the form is built from `config/settings.php` (after `hydrateDynamicSchema()`).
2. Save validates from the schema, merges into existing keys, `SettingService::setMany()`, then forgets `app_settings_channel_{id}` and `api.settings.v1.channel_{id}`.
3. Storefront `GET /api/v1/settings` remembers `SettingResource` forever under `api.settings.v1.channel_{id}` until that forget.
4. System Cache **Clear** runs `cache:clear` plus per-channel settings/locale/catalog cache keys. **Optimize** runs `optimize:clear` then `optimize`. **Revalidate** calls `StorefrontRevalidator::attempt()`. **Refresh all** does those three in order.
5. Admin Theme save writes `admin_theme_settings.options` JSON; layouts call `admin_theme()->htmlAttributeString()` / `styleAttribute()`.

## Schema vs persisted defaults

- `SettingService::getDefaultFromConfig()` and `SettingResource` and the admin form (`_form.blade.php`) read schema key **`default` only**.
- Storefront **theme** color/font fields in `config/settings.php` use **`value`**, not `default`. Until a row exists in `settings`, those keys resolve as **null** (the color input then paints `#000000`).
- `doc/theme-colors.md` maps storefront `theme.*` keys to CSS variables accurately. Its claim that `SettingService` / `SettingResource` fall back to legacy `value` **does not match current PHP**. Do not edit that file from this module’s docs.

`config/settings.php` comments that notification passwords are stored encrypted via `SettingService`. **`SettingService` does not encrypt.** `notifications.mail.password` is a plain string in `settings.value`.

## Documentation index

| File | Contents |
|------|----------|
| [models.md](models.md) | `Setting`, `AdminThemeSetting` |
| [database.md](database.md) | `settings`, `admin_theme_settings` |
| [controllers.md](controllers.md) | Admin settings / theme / cache; API settings |
| [services.md](services.md) | `SettingService`, `AdminThemeService`, `SystemCacheService`, helpers, `FooterSections` |
| [routes.md](routes.md) | Admin + API routes, permissions, artisan side effects |
| [requests-validation.md](requests-validation.md) | Inline validation (no FormRequests) |
| [dependencies.md](dependencies.md) | Callers, `StorefrontRevalidator`, env names |
| [tests.md](tests.md) | `AdminThemeAttributesTest` |

## Schema catalog (`config/settings.php`)

Dot keys as stored in `settings.key`. **Public** = included in `GET /api/v1/settings` (`SettingResource` skips `public` empty/false).

### `general`

| Dot key | Type | Default | Public | Required |
|---------|------|---------|--------|----------|
| `general.app_name` | text | — | yes | yes |
| `general.logo` | file | — | yes | |
| `general.favicon` | file | — | yes | |
| `general.admin_register_enabled` | boolean | `true` | no | |

### `store`

| Dot key | Type | Default | Public | Required |
|---------|------|---------|--------|----------|
| `store.company_name` | text | — | no | yes |
| `store.email` | email | — | yes | |
| `store.phone` | text | — | yes | |
| `store.whatsapp` | text | — | yes | |
| `store.address` | text | — | no | |
| `store.city` | text | — | no | |
| `store.postcode` | text | — | no | |
| `store.country` | text | `AL` | no | |
| `store.vat_number` | text | — | no | |
| `store.registration_number` | text | — | no | |
| `store.bank_name` | text | — | no | |
| `store.bank_iban` | text | — | no | |
| `store.bank_swift` | text | — | no | |
| `store.invoice_prefix` | text | `INV-` | no | |
| `store.invoice_footer_note` | textarea | `Thank you for your order!` | no | |

### `theme` (storefront brand — schema uses `value`, not `default`)

All public, type `color` except fonts (`text`). Schema `value`:

| Dot key | Schema `value` |
|---------|----------------|
| `theme.background_color` | `#ffffff` |
| `theme.primary_color` | `#fcf3ee` |
| `theme.secondary_color` | `#d8bfae` |
| `theme.accent_color` | `#c6a15b` |
| `theme.text_color` | `#2f2420` |
| `theme.muted_text_color` | `#7a6a63` |
| `theme.border_color` | `#efe1da` |
| `theme.button_primary_background_color` | `#b76e79` |
| `theme.button_primary_text_color` | `#ffffff` |
| `theme.button_hover_background_color` | `#8f4f5b` |
| `theme.button_hover_text_color` | `#ffffff` |
| `theme.button_secondary_background_color` | `#fcf3ee` |
| `theme.button_secondary_text_color` | `#2f2420` |
| `theme.button_active_background_color` | `#8f4f5b` |
| `theme.button_active_text_color` | `#ffffff` |
| `theme.primary_font` | `Playfair Display` |
| `theme.secondary_font` | `Inter` |
| `theme.dropdown_background_color` | `#fffaf7` |
| `theme.dropdown_text_color` | `#2f2420` |
| `theme.dropdown_muted_text_color` | `#6f5f58` |
| `theme.dropdown_border_color` | `#e6d4cb` |
| `theme.dropdown_hover_background_color` | `#efe1da` |
| `theme.dropdown_hover_text_color` | `#2f2420` |
| `theme.dropdown_active_background_color` | `#9f5f68` |
| `theme.dropdown_active_text_color` | `#fffaf7` |
| `theme.dropdown_divider_color` | `#e6d4cb` |

Storefront CSS variable mapping: [`doc/theme-colors.md`](../../../doc/theme-colors.md).

### `layout`

| Dot key | Type | Default | Options | Public |
|---------|------|---------|---------|--------|
| `layout.max_width` | number | `1200` | | yes |
| `layout.header_height` | number | `80` | | yes |
| `layout.footer_height` | number | `100` | | yes |
| `layout.border_radius` | number | `8` | | yes |
| `layout.pagination_type` | select | `numeric` | `numeric`, `infinite_scroll`, `load_more_button` | yes |
| `layout.default_sort` | select | `newest` | `newest`, `price_asc`, `price_desc` | yes |

### `header`

| Dot key | Type | Default | Options | Public |
|---------|------|---------|---------|--------|
| `header.top_bar.enabled` | boolean | `true` | | yes |
| `header.top_bar.text` | text | — | | yes |
| `header.top_bar.link` | text | — | | yes |
| `header.top_bar.background_color` | color | `#000000` | | yes |
| `header.top_bar.text_color` | color | `#ffffff` | | yes |
| `header.top_bar.closable` | boolean | `true` | | yes |
| `header.main.sticky` | boolean | `true` | | yes |
| `header.main.transparent_on_home` | boolean | `false` | | yes |
| `header.main.show_search` | boolean | `true` | | yes |
| `header.main.search_placeholder` | text | `Search products...` | | yes |
| `header.main.show_account` | boolean | `true` | | yes |
| `header.main.show_wishlist` | boolean | `true` | | yes |
| `header.main.show_cart` | boolean | `true` | | yes |
| `header.main.cart_style` | select | `drawer` | `icon`, `icon_with_text`, `drawer` | yes |
| `header.menu.enabled` | boolean | `true` | | yes |
| `header.menu.menu_type` | select | `mega` | `simple`, `mega` | yes |
| `header.menu.sticky` | boolean | `false` | | yes |
| `header.menu.show_icons` | boolean | `true` | | yes |

### `footer`

| Dot key | Type | Default | Public |
|---------|------|---------|--------|
| `footer.columns` | number | `4` | yes |
| `footer.sections` | textarea | `Company\nCustomer Care\nLegal\nSocial` | yes |
| `footer.column_content` | footer_column_content | `[]` | yes |
| `footer.general_text` | textarea | `''` | yes |
| `footer.show_socials` | boolean | `true` | yes |

### `seo`

| Dot key | Type | Public |
|---------|------|--------|
| `seo.meta_title` | text | yes |
| `seo.meta_description` | text | yes |
| `seo.og_image` | file | yes |

### `gdpr`

| Dot key | Type | Default | Public |
|---------|------|---------|--------|
| `gdpr.enabled` | boolean | `true` | yes |
| `gdpr.cookie_banner_enabled` | boolean | `true` | yes |
| `gdpr.cookie_banner_title` | text | `We value your privacy` | yes |
| `gdpr.cookie_banner_text` | textarea | `We use cookies to improve your shopping experience, analyze traffic, and personalize content.` | yes |
| `gdpr.privacy_policy_url` | text | `/privacy-policy` | yes |
| `gdpr.terms_and_conditions_url` | text | `/terms-and-conditions` | yes |
| `gdpr.cookie_policy_url` | text | `/cookie-policy` | yes |
| `gdpr.show_reject_button` | boolean | `true` | yes |
| `gdpr.consent_expiry_days` | number | `180` | yes |
| `gdpr.newsletter_consent_text` | textarea | `I agree to receive marketing emails and understand I can unsubscribe at any time.` | yes |
| `gdpr.marketing_default_checked` | boolean | `false` | yes |
| `gdpr.analytics_tracking_enabled` | boolean | `false` | yes |
| `gdpr.analytics_require_consent` | boolean | `true` | yes |
| `gdpr.analytics_anonymize_ip` | boolean | `true` | **no** |
| `gdpr.analytics_retention_days` | number | `180` | **no** |
| `gdpr.consent_version` | text | `v1` | yes |

Which GDPR keys the telemetry module actually reads: [gdpr-storefront-telemetry README](../gdpr-storefront-telemetry/README.md).

### `popup`

| Dot key | Type | Default | Public |
|---------|------|---------|--------|
| `popup.enabled` | boolean | `false` | yes |
| `popup.title` | text | — | yes |
| `popup.description` | text | — | yes |
| `popup.image` | file | — | yes |
| `popup.cta_text` | text | `Shop Now` | yes |
| `popup.cta_link` | text | — | yes |
| `popup.delay` | number | `3` | yes |
| `popup.auto_close` | boolean | `false` | yes |
| `popup.auto_close_delay` | number | `5` | yes |
| `popup.allow_manual_close` | boolean | `true` | yes |
| `popup.show_once` | boolean | `true` | yes |
| `popup.background_color` | color | `#ffffff` | yes |
| `popup.text_color` | color | `#000000` | yes |

### `tax`

Documented with calculation behavior in [tax](../tax/README.md). Schema:

| Dot key | Type | Default | Public |
|---------|------|---------|--------|
| `tax.pricing.price_includes_tax` | boolean | `true` | yes |
| `tax.calculation.discounts_reduce_taxable_amount` | boolean | `true` | no |
| `tax.shipping.taxable` | boolean | `false` | no |
| `tax.shipping.tax_class_code` | text (select of `TaxClass.code` when any exist) | `standard` | no |
| `tax.origin.country` | text | `AL` | no |
| `tax.origin.state` | text | `''` | no |
| `tax.origin.postcode` | text | `''` | no |

### `system` and `checkout`

Two guest-checkout flags exist. [cart-checkout](../cart-checkout/README.md) `CheckoutAddressFields::guestCheckoutEnabled()` prefers `checkout.guest_enabled`, then `system.checkout`. Payment drivers (`CashOnDeliveryDriver`, `PcbBankDriver`, `RaiAcceptDriver`) read **`system.checkout` only**.

| Dot key | Type | Default | Public | Notes |
|---------|------|---------|--------|--------|
| `system.cache_enabled` | boolean | `true` | no | **No `setting('system.cache_enabled')` reader in `app/`** |
| `system.debug_mode` | boolean | `false` | no | **No `setting('system.debug_mode')` reader in `app/`** |
| `system.checkout` | boolean | `true` | yes | Label “Checkout Guest Enabled” |
| `checkout.guest_enabled` | boolean | `true` | yes | Same intent as `system.checkout` |
| `checkout.address_fields.label.show` | boolean | `true` | yes | |
| `checkout.address_fields.company.show` | boolean | `true` | yes | |
| `checkout.address_fields.nui.show` | boolean | `true` | yes | |
| `checkout.address_fields.nui.required_if_company` | boolean | `true` | yes | |
| `checkout.address_fields.phone.show` | boolean | `true` | yes | |
| `checkout.address_fields.phone.required` | boolean | `false` | yes | |
| `checkout.address_fields.city.show` | boolean | `true` | yes | |
| `checkout.address_fields.city.required` | boolean | `true` | yes | |
| `checkout.address_fields.state_code.show` | boolean | `false` | yes | |
| `checkout.address_fields.address2.show` | boolean | `true` | yes | |
| `checkout.address_fields.postcode.show` | boolean | `true` | yes | |
| `checkout.address_fields.postcode.required` | boolean | `false` | yes | |

### `product` / `category`

| Dot key | Type | Default | Options | Public |
|---------|------|---------|---------|--------|
| `product.catalog.default_view` | select | `grid` | `grid`, `list` | yes |
| `product.catalog.allow_view_switch` | boolean | `true` | | yes |
| `product.catalog.columns.mobile` | number | `2` | | yes |
| `product.catalog.columns.tablet` | number | `3` | | yes |
| `product.catalog.columns.desktop` | number | `4` | | yes |
| `product.catalog.mobile_uneven_layout` | boolean | `false` | | yes |
| `product.catalog.products_per_page` | number | `16` | | yes |
| `product.catalog.facets.show_price` | boolean | `true` | | yes |
| `product.catalog.facets.show_categories` | boolean | `true` | | yes |
| `product.catalog.facets.show_attributes` | boolean | `true` | | yes |
| `product.catalog.search.enable_advanced_search` | boolean | `false` | | yes |
| `product.catalog.search.use_fulltext` | boolean | `true` | | **no** |
| `product.catalog.search.cache_ttl` | number | `120` | | **no** |
| `product.external_order.mode` | select | `manual` | `manual`, `auto` | **no** |
| `product.card.show_add_to_cart` | boolean | `true` | | yes |
| `product.card.show_rating` | boolean | `true` | | yes |
| `product.details.show_rating` | boolean | `true` | | yes |
| `product.details.show_related_products` | boolean | `true` | | yes |
| `product.details.show_upsell_products` | boolean | `true` | | yes |
| `product.details.show_crosssell_products` | boolean | `true` | | yes |
| `category.icons.show_icons` | boolean | `true` | | yes |
| `category.icons.icon_scope` | select | `first_level` | `all`, `first_level` | yes |
| `category.tree.max_depth` | number | `3` | | yes |

Typed readers: `CatalogFacetSettings`, `CatalogSearchSettings`, `ExternalCatalogOrderSettings` (not owned here).

### `notifications`

All **not public** except `notifications.user.email_verification` and `notifications.user.password_reset`.

| Dot key | Type | Default | Options / required |
|---------|------|---------|--------------------|
| `notifications.mail.driver` | select | `smtp` | `smtp`, `sendmail`, `mailgun`, `ses`, `postmark`, `log`, `array` |
| `notifications.mail.host` | text | — | |
| `notifications.mail.port` | number | `587` | |
| `notifications.mail.encryption` | select | `tls` | `tls`, `ssl`, `none` |
| `notifications.mail.username` | text | — | |
| `notifications.mail.password` | password | — | stored plaintext |
| `notifications.mail.from_address` | email | — | **required** |
| `notifications.mail.from_name` | text | `Commerce` | |
| `notifications.admin.email` | email | — | **required** |
| `notifications.admin.notify_new_order` | boolean | `true` | |
| `notifications.admin.notify_new_user` | boolean | `true` | |
| `notifications.admin.notify_low_stock` | boolean | `true` | |
| `notifications.admin.low_stock_threshold` | number | `5` | **no `setting()` reader in `app/`** (product admin filter uses a request field, not this key) |
| `notifications.admin.notify_new_review` | boolean | `false` | |
| `notifications.admin.notify_contact_form` | boolean | `true` | |
| `notifications.user.welcome_email` | boolean | `true` | |
| `notifications.user.email_verification` | boolean | `false` | public |
| `notifications.user.login_alert` | boolean | `false` | |
| `notifications.user.password_reset` | boolean | `true` | public |
| `notifications.order.placed` | boolean | `true` | |
| `notifications.order.confirmed` | boolean | `true` | |
| `notifications.order.processing` | boolean | `false` | |
| `notifications.order.shipped` | boolean | `true` | |
| `notifications.order.delivered` | boolean | `true` | |
| `notifications.order.cancelled` | boolean | `true` | |
| `notifications.order.refunded` | boolean | `true` | |
| `notifications.order.invoice_attached` | boolean | `true` | **no PHP reader** (see orders notifications docs) |
| `notifications.cart.abandoned_email` | boolean | `false` | |
| `notifications.cart.abandoned_delay_hours` | number | `1` | **no `setting()` reader in `app/`** |
| `notifications.cart.recovery_coupon` | boolean | `false` | **no `setting()` reader in `app/`** |

Mailables read `notifications.mail.from_address` / `from_name`. There is **no** `app/` code that assigns `notifications.mail.driver|host|port|encryption|username|password` onto `config('mail')`. Laravel mailer remains `MAIL_*` / `config/mail.php`.

## Env vars (names only)

Read through config by this module’s cache/revalidate path:

| Name | Config key | Used by |
|------|------------|---------|
| `CACHE_STORE` | `config('cache.default')` | `SystemCacheService::status()` (`cache_driver`) |
| `STOREFRONT_REVALIDATE_URL` | `config('external_catalog.revalidate_url')` | `SystemCacheService::status()` / `revalidateStorefront()` |
| `STOREFRONT_REVALIDATE_SECRET` | `config('external_catalog.revalidate_secret')` | enabled check + `StorefrontRevalidator` (value never logged by `SystemCacheService`) |
| `STOREFRONT_REVALIDATE_TAGS` | `config('external_catalog.revalidate_tags')` | status display; default tag list when revalidate is called with `$tags = null` |

No other env names are read by `SettingService`, `AdminThemeService`, or the settings/theme controllers.
