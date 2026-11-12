# Tests

There are **no** PHPUnit tests whose subject is `Admin\CountryController`, `Api\V1\CountryController`, `CountriesNowImportService`, `Country`, `City`, `CityExternalId`, `CountrySeeder`, or `CitySeeder`.

`tests/` was searched for those class names, `CountriesNow`, `city_external_ids`, `shipping_group_id` on countries, and `/api/v1/countries`. No dedicated matches.

## Incidental uses (not this module)

### `Tests\Feature\Bundle\BundleCartCheckoutTest`

Path: `tests/Feature/Bundle/BundleCartCheckoutTest.php`

Place-order helper sets cart address JSON `'city' => 'City', 'country' => 'XK'`. That is a checkout address payload, not an assertion on `countries` / `cities` tables. The test does not create a `Country` row.

### `Tests\Unit\CheckoutAddressFieldsTest`

Path: `tests/Unit/CheckoutAddressFieldsTest.php`

Asserts checkout **field** rules for `billing.city` (show/required/hidden). It does not touch `App\Models\City` or country catalog uniqueness.

## PHPUnit defaults

`phpunit.xml` sets `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. No geo-countries feature tests exist to skip or run against MySQL.

`CACHE_STORE=array` in phpunit env; this module does not cache country lists.

## Factories

No `CountryFactory`, `CityFactory`, or `CityExternalIdFactory` under `database/factories/`.
