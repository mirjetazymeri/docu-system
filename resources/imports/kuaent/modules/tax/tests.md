# Tax tests

There are **no** Feature tests for `TaxClassController` / `TaxRateController`, and **no** unit tests for `TaxService` or `TaxSettings`. Catalog CRUD and zone matching are untested in-repo.

Coverage that exists is the **shared pricing** tax extraction / order totals path used by `TaxService`.

## TaxInclusivePriceCalculatorTest

**File:** `tests/Unit/Pricing/TaxInclusivePriceCalculatorTest.php`

PHPUnit `TestCase` (no Laravel application / database). `setUp` builds:

- `TaxInclusivePriceCalculator`
- `OrderTotalsCalculator` wired with that calculator

Rates in tests are `TaxRateInput` values (not `TaxRate` models).

| Method | Asserts |
|--------|---------|
| `test_scenario_1_single_product` | `extract(120, 20%)` → net `100.00`, tax `20.00`, gross `120.00` (rounded 2) |
| `test_scenario_2_quantity` | Inclusive line `360` at 20% → net `300.00`, tax `60.00`, grand `360.00` |
| `test_scenario_3_percentage_discount` | Discount `12` on `120` @ 20%, `discountsReduceTaxable` → net `90.00`, tax `18.00`, grand `108.00` |
| `test_scenario_4_fixed_discount_rounding` | Discount `10` on `120` @ 20% → net `91.67`, tax `18.33` (comment: half-up), grand `110.00` |
| `test_scenario_5_mixed_rates` | Two lines 20% and 7% → taxes `20.00` + `7.00`, grand `227.00`, two breakdown rows |
| `test_scenario_6_zero_tax_product` | Rate `0` on `50` → net `50.00`, tax `0.00` |
| `test_scenario_7_taxed_shipping_inclusive` | Line `120` + shipping `12` both 20% inclusive → items tax `20.00`, shipping tax `2.00`, total tax `22.00`, grand `132.00` |
| `test_scenario_8_untaxed_shipping` | Same line, shipping `12` not taxable → total tax `20.00`, shipping tax `0.00`, grand `132.00` |
| `test_scenario_9_order_discount_across_mixed_rates` | Discount `22.70` across 20% and 7% lines; allocated discounts sum to `22.70`; each line net+tax equals gross−discount |
| `test_does_not_add_tax_twice_when_prices_include_tax` | Inclusive grand stays `120.00`, not `140.00` |
| `test_multi_non_compound_rates_use_combined_extraction` | Two 10% simple rates on inclusive `120` → tax `20.00` (combined 20%, not stacked extraction) |

`priceIncludesTax: true` and `discountsReduceTaxable: true` in all `OrderTotalsCalculator` scenarios except where shipping flags differ.

Run:

```bash
php artisan test tests/Unit/Pricing/TaxInclusivePriceCalculatorTest.php
```

## Related (not tax-module tests)

`tests/Unit/Pricing/DiscountEligibilityAndRefundTest.php` uses `TaxRateInput` + `OrderTotalsCalculator` to assert discount eligibility affects line tax, and that credit-note quotes scale stored order line tax. That file tests checkout refund/discount behavior, not admin tax CRUD or `TaxService` matching.
