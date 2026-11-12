# Tests — Coupons and Promotions

Module index: [README.md](./README.md)

Searched `tests/` for `Coupon`, `Promotion`, `coupon`, `promotion` (case-insensitive). **No matches.**

No `database/factories` files for Coupon or Promotion.

---

## Related test (pricing, not coupon/promotion classes)

### `tests/Unit/Pricing/DiscountEligibilityAndRefundTest.php`

PHPUnit `TestCase` (not Laravel application test). Does not instantiate `Coupon`, `CouponService`, or `Promotion`.

| Test | Type | Covers |
| ---- | ---- | ------ |
| `test_discount_allocates_only_to_eligible_lines` | Unit | `OrderTotalsCalculator` with `discount_eligible` true/false per line. Eligible line gets the full `discountTotal`; ineligible gets `0`. |
| `test_partial_line_refund_is_proportional` | Unit | `CreditNoteService::quote` qty split — refunds, not coupons |
| `test_refund_tax_does_not_exceed_original_line_tax_on_full_qty` | Unit | Credit note tax cap |

**Why it is related:** `TaxService::calculate` sets `discount_eligible` from `CouponService::eligibleCartItemIds` when a coupon is on the cart. The calculator test therefore documents the **allocation math** used after coupon quote, not coupon validation, usage limits, or promotions.

---

## Apparently not covered (after repository search)

No unit/feature/API tests were found for:

- Admin coupon/promotion CRUD or bulk generate
- `CouponService` find/quote/apply/remove / channel / guest / limits / scoping
- `PromotionService` media, translations, `product_prices` write-back, projections
- `Api\V1\PromotionController` list/show/products or cache keys
- `PromotionWidgetResolver`
- `POST/DELETE /api/v1/cart/coupon`

---

## How to run the related unit test

```text
php artisan test tests/Unit/Pricing/DiscountEligibilityAndRefundTest.php
```
