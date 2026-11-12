# Tests

There are **no** dedicated tests for CMS pages, widgets, menus, resolvers, or `FooterSections`.

`tests/` (as of this writing) covers catalog/checkout/pricing/theme chrome, not this module:

- Feature: bundle, configurable product/cart, `ExampleTest`
- Unit: external catalog export, person name, pricing, checkout address fields, product extras, **admin theme attributes**

`tests/Unit/AdminThemeAttributesTest.php` asserts CSS data attributes such as `data-menu-styles` and `data-page-style`. Those are **admin UI theme** options, not `MenuController` or CMS `Page` records.

No PHPUnit files reference `PageController`, `WidgetController`, `MenuController`, `WidgetRenderer`, or the `App\Services\Cms` namespace.

## Implied gaps

Worth covering if tests are added later (not implemented):

- `Page::published()` vs future `published_at`
- Nested `GET /api/v1/pages/{path}` vs numeric id
- Channel mismatch on attach / widget show 404
- `random_products` cache bypass and `refreshPagePayload`
- `promotionsVersionStamp` on page cache keys
- Menu footer section inheritance and `FooterSections::parse`
- `GET /widgets/home` ignoring channel vs page show filtering widget channels
- Stale `PageSeeder` (should not be relied on)
