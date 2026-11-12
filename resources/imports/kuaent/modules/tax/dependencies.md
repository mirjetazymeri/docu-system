# Tax dependencies

This module’s **contribution** to checkout/orders is the quote from `TaxService::calculate()` and the settings/domain math that produce it. Callers persist and display that quote; they own cart/order workflows.

## Inbound (this module uses)

| Dependency | How tax uses it |
|------------|-----------------|
| `CouponService::eligibleCartItemIds` | Restricts which cart lines receive the coupon discount before tax |
| `Coupon` model | Loaded by `coupon_id` on the cart |
| `Cart` / `CartItem` / `Product` | Lines, `row_total`, `tax_class_id`, addresses, `shipping.amount`, coupon fields, deposits |
| `OrderTotalsCalculator`, `TaxInclusivePriceCalculator`, `Money`, `DiscountAllocator` | Shared pricing domain (no I/O) |
| `setting()` / `SettingService` | `TaxSettings` |
| `Country` | Optional name on admin rate list (`TaxRate::country()`) |
| Yajra DataTables | Admin indexes |
| Spatie/access-level permissions | `check.permission:*` |

## Outbound (who calls this module)

### Cart recalc — `App\Services\Checkout\CartService::recalcTotals`

Calls `$this->tax->calculate($cart->load('items'))`.

Writes cart columns from the quote: `subtotal`, `discount_total`, `shipping_total`, `deposit_total`, `tax_total`, `grand_total`, plus `items_count`.

Stores the full quote under `cart.meta.tax_quote`:

`items_tax`, `shipping_tax`, `total_tax`, `breakdown`, `line_taxes`, `line_discounts`, `line_nets`, `items_net`, `discount_net`, `discount_tax`, `shipping_net`, `price_includes_tax`, `subtotal`, `discount_total`, `shipping_total`, `deposit_total`, `grand_total`.

Comment in code: when prices include tax, `tax_total` is extracted for display and is not added again into `grand_total` (that rule is implemented in `OrderTotalsCalculator`, not by adding tax in `CartService`).

### Place order — `App\Services\Checkout\OrderService`

At place-time (comment: never trust client totals):

```text
$taxQuote = $this->tax->calculate($cart->load('items.product'));
```

**Order row**

| Order column | Quote key |
|--------------|-----------|
| `subtotal` | `subtotal` |
| `discount_total` | `discount_total` |
| `shipping_total` | `shipping_total` |
| `deposit_total` | `deposit_total` |
| `tax_total` | `total_tax` |
| `grand_total` | `grand_total` |
| `coupon_discount` | same as `discount_total` |
| `prices_include_tax` | `price_includes_tax` (default true if missing) |
| `tax_breakdown` | `breakdown` |

**Each order item** (keyed by **cart item id**)

| Order item | Quote |
|------------|--------|
| `discount` | `line_discounts[id]` |
| `tax` | `line_taxes[id]` |
| `meta.net_total` | `line_nets[id]` |
| `meta.tax_total` | `line_taxes[id]` |

Merchandise `unit_price` / `row_total` remain the cart line prices (gross as stored on the cart), not re-derived here.

### Sales specialist preview — `App\Services\SalesSpecialist\SalesSpecialistOrderService`

`previewCart` / `previewFulfillment` go through `CartService::recalcTotals`, then `buildPreviewResponse` calls `TaxService::calculate` again for `tax_breakdown`. `price_includes_tax` in that payload comes from `tax_config('price_includes_tax', true)`, not from the quote array. Placement uses `OrderService::place` (same snapshot as storefront).

### API presentation (read quote / snapshot)

| Caller | Behavior |
|--------|----------|
| `CartResource` | `FinancialTotalsResource::fromTaxQuote(meta.tax_quote)`; empty quote falls back to cart columns + `tax_config('price_includes_tax')` |
| `CartController::totals` | Recalc, then same `fromTaxQuote` |
| `OrderResource` | Uses `FinancialTotalsResource::fromOrder` (`prices_include_tax`, `tax_breakdown`, item `tax` sums) |
| Admin `resources/views/admin/orders/_totals.blade.php` | Displays `tax_total` and each `tax_breakdown` row |
| Invoice view | Labels depend on `prices_include_tax`; lists `tax_breakdown` |

### Credit notes

`CreditNoteService` copies `prices_include_tax` from the order and builds a new `tax_breakdown` from refunded line taxes. It does **not** call `TaxService`. Historical tax is whatever was snapshotted on the order/items.

### Catalog

`ProductService` persists `tax_class_id`. `ProductController` lists classes for the product form. Products without a class produce zero line tax.

## Settings UI

`SettingController::hydrateDynamicSchema()` injects tax class codes into `tax.shipping.tax_class_code` when at least one class exists.

## What is not a tax-module API

Storefront checkout routes (`/api/v1/cart`, place-order, etc.) belong to checkout. They trigger `CartService::recalcTotals` / `OrderService::place`, which is how tax is applied.

## Package / PHP

Tax math uses BCMath via `App\Domain\Pricing\Money`. Admin lists use `yajra/laravel-datatables`.
