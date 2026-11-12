# Requests and validation

There are **no** Form Request classes for this module (`app/Http/Requests` has no Country/City request). `App\Http\Controllers\Admin\CountryController` calls `$request->validate(...)`. The API controller has no request validation.

## `App\Http\Controllers\Admin\CountryController`

### `import`

| Field | Rules |
| --- | --- |
| `with_cities` | `sometimes\|boolean` |

Then `$request->boolean('with_cities', true)` is passed to `CountriesNowImportService::import`. If the field is absent, the default is `true`.

### `validateCountry` (`store` / `update`)

| Field | Rules |
| --- | --- |
| `code` | `required`, `string`, `size:2`, `unique:countries,code` (update: `Rule::unique(...)->ignore($ignoreId)`) |
| `name` | `required`, `string`, `max:120` |
| `is_default` | `sometimes`, `boolean` |
| `shipping_group_id` | `nullable`, `integer`, `Rule::exists('shipping_groups', 'id')` |

After validate:

- `code` = `Str::upper(trim(...))`
- `name` = `trim(...)`
- `is_default` = `$request->boolean('is_default')` (unchecked checkbox → false)
- `shipping_group_id` = integer if `filled`, else `null`

Empty select “Unassigned” clears the FK.

### `storeCity` / `updateCity`

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:120`, `Rule::unique('cities', 'name')->where(country_id)` (update: `->ignore($city->id)`) |
| `beki_external_id` | `nullable`, `string`, `max:64` |

Name is trimmed on write. `beki_external_id` is not a column; it is passed to `City::syncExternalId(CityExternalId::PROVIDER_BEKI, …)`. Blank value deletes the `beki` row.

`delete`, `deleteCity`, `setDefault`, `create`, `edit`, `importCities` have no Form validation. Missing ids → `findOrFail` (404).

## Related validation outside this module

Not owned here; listed because they reference `countries`:

| Location | Rule |
| --- | --- |
| `App\Http\Requests\Admin\UserAddress\StoreUserAddressRequest` (and `UpdateUserAddressRequest`) | `country_code` `required\|string\|size:2\|exists:countries,code` |
| `App\Http\Controllers\Api\V1\AddressController` | same `exists:countries,code` on write |
| `App\Http\Requests\Admin\PaymentMethod\StorePaymentMethodRequest` / `UpdatePaymentMethodRequest` | `countries.*` `Rule::in` of `Country::pluck('code')` uppercased |
| `App\Http\Requests\Admin\ShippingMethod\StoreShippingMethodRequest` (`UpdateShippingMethodRequest` extends it) | same `countries.*` `Rule::in` |
| `App\Http\Controllers\Admin\ShippingGroupController::update` | `country_ids.*` `integer\|exists:countries,id` |
| `App\Http\Requests\Admin\Tax\StoreTaxRateRequest` (`UpdateTaxRateRequest` extends it; same `country_iso2` rule) | `country_iso2` `nullable\|string\|size:2` — **no** `exists:countries` |
| `App\Support\CheckoutAddressFields` | `billing.country` / `shipping.country` `string\|size:2` — **no** `exists:countries` |
