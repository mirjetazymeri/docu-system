# Routes — Coupons and Promotions

Module index: [README.md](./README.md)

---

## Web (admin)

Group (`routes/web.php`):

```text
middleware: auth, verified.if-required, set.user.locale, role:Admin
prefix: admin
name: admin.
```

Plus per-action `check.permission:*` on the controller.

| Method | URI | Name | Controller | Permission |
| ------ | --- | ---- | ---------- | ---------- |
| GET | `/admin/coupons` | `admin.coupons.index` | `CouponController@index` | `coupon_view` |
| GET | `/admin/coupons/create` | `admin.coupons.create` | `CouponController@create` | `coupon_create` |
| POST | `/admin/coupons` | `admin.coupons.store` | `CouponController@store` | `coupon_create` |
| GET | `/admin/coupons/edit/{id}` | `admin.coupons.edit` | `CouponController@edit` | `coupon_edit` |
| PUT | `/admin/coupons/{id}` | `admin.coupons.update` | `CouponController@update` | `coupon_edit` |
| DELETE | `/admin/coupons/{id}` | `admin.coupons.delete` | `CouponController@delete` | `coupon_delete` |
| GET | `/admin/coupons/generate` | `admin.coupons.generate.form` | `CouponController@generateForm` | `coupon_create` |
| GET | `/admin/promotions/product-pricing` | `admin.promotions.product_pricing` | `PromotionController@productPricing` | `promotion_view` |
| GET | `/admin/promotions` | `admin.promotions.index` | `PromotionController@index` | `promotion_view` |
| GET | `/admin/promotions/create` | `admin.promotions.create` | `PromotionController@create` | `promotion_create` |
| POST | `/admin/promotions` | `admin.promotions.store` | `PromotionController@store` | `promotion_create` |
| GET | `/admin/promotions/edit/{id}` | `admin.promotions.edit` | `PromotionController@edit` | `promotion_edit` |
| PUT | `/admin/promotions/{id}` | `admin.promotions.update` | `PromotionController@update` | `promotion_edit` |
| DELETE | `/admin/promotions/{id}` | `admin.promotions.delete` | `PromotionController@delete` | `promotion_delete` |
| GET | `/admin/lookups/promotions` | `admin.lookups.promotions` | `LookupController@promotions` | `lookup_view` |

### Bulk generate — routed surface

Only `GET admin/coupons/generate` exists. It redirects to `admin.coupons.create?mode=bulk`. There is **no** POST generate route. `CouponController::generate()` is not bound to a route.

Sidebar: `resources/views/layouts/components/main-sidebar.blade.php` — Promotions menu (`promotion_view` → campaigns, `coupon_view` → coupons). Catalog rules and newsletters share that menu but are other modules.

---

## API

Group (`routes/api.php`): prefix `v1` (URI `/api/v1/...`), middleware `channel.api`, `locale.api`.

| Method | URI | Controller | Auth |
| ------ | --- | ---------- | ---- |
| GET | `/api/v1/promotions` | `Api\V1\PromotionController@index` | none |
| GET | `/api/v1/promotions/{idOrSlug}/products` | `products` | none |
| GET | `/api/v1/promotions/{idOrSlug}` | `show` | none |
| POST | `/api/v1/cart/coupon` | `Api\V1\CartController@applyCoupon` | optional Sanctum + cart token |
| DELETE | `/api/v1/cart/coupon` | `Api\V1\CartController@removeCoupon` | optional Sanctum + cart token |

Cart coupon routes are registered in the same public `v1` group as cart (not inside `auth:sanctum`). Cart identity: `X-Cart-Token` header or `cart_token` query; Sanctum user attaches/merges guest cart.

Widget/page endpoints that embed promotions are documented under [cms-pages-widgets](../cms-pages-widgets/README.md).

See [api.md](./api.md) for request/response shapes.
