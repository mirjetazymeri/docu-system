# Routes

## Admin (`routes/web.php`)

Group: `auth`, `verified.if-required`, `set.user.locale`, `role:Admin`, prefix `admin`, name `admin.`. Web group also runs `SetCurrentChannel`.

```
GET    /admin/pages                          admin.pages.index
GET    /admin/pages/create                   admin.pages.create
POST   /admin/pages                          admin.pages.store
GET    /admin/pages/{page}                   admin.pages.show   (registered; no controller method)
GET    /admin/pages/{page}/edit              admin.pages.edit
PUT    /admin/pages/{page}                   admin.pages.update
PATCH  /admin/pages/{page}                   admin.pages.update
DELETE /admin/pages/{page}                   admin.pages.destroy

POST   /admin/pages/{page}/widgets/attach    admin.pages.widgets.attach
DELETE /admin/pages/{page}/widgets/{pivotId} admin.pages.widgets.detach
POST   /admin/pages/{page}/widgets/reorder   admin.pages.widgets.reorder
POST   /admin/pages/{page}/widgets/{pivotId}/toggle  admin.pages.widgets.toggle
POST   /admin/pages/reorder                  admin.pages.reorder

GET    /admin/widgets                        admin.widgets.index
GET    /admin/widgets/create                 admin.widgets.create
POST   /admin/widgets                        admin.widgets.store
GET    /admin/widgets/{widget}/edit          admin.widgets.edit
PUT    /admin/widgets/{widget}               admin.widgets.update
PATCH  /admin/widgets/{widget}               admin.widgets.update
DELETE /admin/widgets/{widget}               admin.widgets.destroy
POST   /admin/widgets/reorder                admin.widgets.reorder
```

Controller actions type-hint `int $id` / `int $pageId`; the resource parameter name is `{page}` / `{widget}`.

Widget resource uses `->except(['show'])`.

### Lookups used by the widget form

```
GET /admin/lookups/promotions
GET /admin/lookups/products
GET /admin/lookups/categories
GET /admin/lookups/attributes
GET /admin/lookups/attribute/{id}/options
```

Names: `admin.lookups.promotions` (etc.).

### Sitemap (not under `/admin`)

Middleware `locale.api` + `channel.api`:

```
GET /sitemaps/pages.xml   SitemapController@pages
```

## Public API (`routes/api.php`)

Prefix `v1`, middleware `channel.api`, `locale.api`.

```
GET /api/v1/pages/{slugOrPath}     PageController@show     where slugOrPath = .*
GET /api/v1/menus                  MenuController@index    ?location=header|footer
GET /api/v1/menus/{location}       MenuController@show
GET /api/v1/widgets/{id}           WidgetController@show   whereNumber('id')
GET /api/v1/widgets                WidgetController@byPlacement
GET /api/v1/widgets/home           WidgetController@home
```

Registration order: `widgets/{id}` (numeric) then `widgets` then `widgets/home`. Non-numeric `home` does not match `{id}`.

Related (not defined in this module, consumed by it):

```
GET  /api/v1/seo/{entity}/{slug}   entity page|pages — same path walk as pages
POST /api/v1/contact               contact_form submit (throttle:10,1)
GET  /api/v1/promotions            storefront promotion listing (not widget CRUD)
GET  /api/v1/products-projection   storefront hydrates product widgets
```

## Permissions (sidebar)

`ModulePermissionSeeder`: module **Pages** → `page_view|create|edit|delete`; **Widgets** → `widget_view|create|edit|delete`.

Admin sidebar group also includes storefront events (`storefront_event_view`); that is analytics, not CMS CRUD.

## Not CMS

`GET /widgets` → theme `WidgetsController@widgets` (unauthenticated demo view).
