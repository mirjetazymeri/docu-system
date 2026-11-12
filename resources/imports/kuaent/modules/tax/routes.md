# Tax routes

There are **no** public API routes for tax class/rate CRUD. Checkout APIs consume `TaxService` via cart/order services (see [dependencies.md](dependencies.md)).

## Admin group

**File:** `routes/web.php`

Group middleware and prefix:

- Middleware: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`
- Prefix: `admin`
- Name prefix: `admin.`

Additional per-action middleware: `check.permission:{name}` on the controllers (`App\Http\Middleware\AutoAuthorizePermission`, alias in `bootstrap/app.php`). Super-admin users skip the permission check. Others need the named permission via access level.

## Tax classes

| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/admin/tax-classes` | `admin.tax_classes.index` | `index` (HTML or DataTables JSON) |
| GET | `/admin/tax-classes/create` | `admin.tax_classes.create` | `create` |
| POST | `/admin/tax-classes` | `admin.tax_classes.store` | `store` |
| GET | `/admin/tax-classes/edit/{id}` | `admin.tax_classes.edit` | `edit` |
| PUT | `/admin/tax-classes/{id}` | `admin.tax_classes.update` | `update` |
| DELETE | `/admin/tax-classes/{id}` | `admin.tax_classes.delete` | `delete` |

`{id}` is an integer tax class id (`findOrFail`).

## Tax rates

| Method | URI | Name | Controller |
|--------|-----|------|------------|
| GET | `/admin/tax-rates` | `admin.tax_rates.index` | `index` (HTML or DataTables JSON) |
| GET | `/admin/tax-rates/create` | `admin.tax_rates.create` | `create` |
| POST | `/admin/tax-rates` | `admin.tax_rates.store` | `store` |
| GET | `/admin/tax-rates/edit/{id}` | `admin.tax_rates.edit` | `edit` |
| PUT | `/admin/tax-rates/{id}` | `admin.tax_rates.update` | `update` |
| DELETE | `/admin/tax-rates/{id}` | `admin.tax_rates.delete` | `delete` |

Index query params (HTML + forwarded by DataTables `ajax.data`): `q`, and for rates also `country`, `active`.

## Permissions

Seeded in `database/seeders/ModulePermissionSeeder.php` as `{resource}_{action}`:

| Module name (seeder) | Permissions |
|----------------------|-------------|
| Tax Classes | `tax_class_view`, `tax_class_create`, `tax_class_edit`, `tax_class_delete` |
| Tax Rates | `tax_rate_view`, `tax_rate_create`, `tax_rate_edit`, `tax_rate_delete` |

Settings UI that includes the `tax` schema group uses `setting_view` / `setting_edit` (`SettingController`).

## Settings keys (not routes)

Stored through the generic settings save endpoint. Schema: `config/settings.php` → `'tax'`.

| Dot key | Type in schema | Default | Public |
|---------|----------------|---------|--------|
| `tax.pricing.price_includes_tax` | boolean | `true` | yes |
| `tax.calculation.discounts_reduce_taxable_amount` | boolean | `true` | no |
| `tax.shipping.taxable` | boolean | `false` | no |
| `tax.shipping.tax_class_code` | text (select of class codes when classes exist) | `standard` | no |
| `tax.origin.country` | text | `AL` | no |
| `tax.origin.state` | text | `''` | no |
| `tax.origin.postcode` | text | `''` | no |

If `tax.origin.country` is empty at runtime, `TaxSettings` uses `store.country` then `'AL'`.
