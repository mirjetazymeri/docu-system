# Newsletter and contact — Controllers

Module README: [README.md](README.md) · Routes: [routes.md](routes.md) · API: [api.md](api.md) · Validation: [requests-validation.md](requests-validation.md)

---

## `App\Http\Controllers\Api\V1\NewsletterController`

Source: `app/Http/Controllers/Api/V1/NewsletterController.php`

### Responsibility

Public JSON: create-or-reactivate a subscriber for the resolved API channel; mark unsubscribed by token or by email+channel.

### Dependencies

No constructor injection. Methods type-hint `SubscribeNewsletterRequest` / `UnsubscribeNewsletterRequest` and `NewsletterSubscriberService`. Reads `app('currentChannel')`.

### Authentication

Routes sit on the public `v1` group (`channel.api`, `locale.api`) and are **not** inside `auth:sanctum`. FormRequests `authorize()` return `true`. Optional Sanctum users are not read.

### Methods

#### `subscribe(SubscribeNewsletterRequest $request, NewsletterSubscriberService $service)`

- **Route:** `POST /api/v1/newsletter/subscribe` (unnamed)
- **Permissions:** none
- **Validation:** [SubscribeNewsletterRequest](requests-validation.md)
- **Behavior:**
  1. `$channel = app('currentChannel')`. If falsy → HTTP **400** JSON `success: false`, `message: Channel not resolved`.
  2. `$service->subscribe(validated email, validated name, (int) $channel->id, 'api', meta)`.
  3. Meta keys: `ip` = `$request->ip()`, `user_agent` = `(string) $request->userAgent()`.
- **Response:** HTTP **201**

```text
success          true
message          "Newsletter subscription saved."
data.id
data.email
data.status
data.unsubscribe_token
meta.channel_id  (int) current channel id
```

#### `unsubscribe(UnsubscribeNewsletterRequest $request, NewsletterSubscriberService $service)`

- **Route:** `POST /api/v1/newsletter/unsubscribe` (unnamed)
- **Permissions:** none
- **Validation:** [UnsubscribeNewsletterRequest](requests-validation.md)
- **Behavior:**
  1. Same channel check as subscribe (400 if missing). Channel is required even when unsubscribing by token.
  2. If `$request->filled('token')` → `unsubscribeByToken(validated token)` (token lookup is **not** scoped to the current channel).
  3. Else → `unsubscribeByEmail(validated email, (int) $channel->id)`.
  4. Null subscriber → HTTP **404** `success: false`, `message: Subscriber not found.`
- **Response:** HTTP **200** (Laravel default)

```text
success          true
message          "Newsletter subscription removed."
data.id
data.email
data.status
meta.channel_id  (int) $subscriber->channel_id  (not necessarily the request channel when using token)
```

No events, jobs, or mail from this controller.

---

## `App\Http\Controllers\Api\V1\ContactController`

Source: `app/Http/Controllers/Api/V1/ContactController.php`

### Responsibility

Accept a public contact payload and call `MailService::forwardContactForm`. Does not write to the database.

### Methods

#### `send(ContactFormRequest $request, MailService $mail)`

- **Route:** `POST /api/v1/contact` (unnamed)
- **Extra middleware:** `throttle:10,1` (10 attempts per 1 minute; Laravel named limiter on this route)
- **Permissions:** none
- **Validation:** [ContactFormRequest](requests-validation.md)
- **Behavior:** `$data = $request->validated()` then:

```text
$mail->forwardContactForm(
    senderName:  $data['name'],
    senderEmail: $data['email'],
    subject:     $data['subject'],
    message:     $data['message'],
);
```

`phone` is not passed. Return value of `forwardContactForm` is not inspected (`void`).

- **Response:** HTTP **200**

```text
success  true
message  "Your message has been sent. We will get back to you shortly."
```

This JSON is returned even when mail is skipped or send fails inside `MailService` ([notifications.md](notifications.md)).

---

## `App\Http\Controllers\Admin\NewsletterSubscriberController`

Source: `app/Http/Controllers/Admin/NewsletterSubscriberController.php`

### Responsibility

Blade admin: filterable DataTables list, create, edit, delete. All queries are channel-scoped via `subscriberQuery()` / `forChannel($this->channelId())`.

### Constructor middleware

`check.permission` → `App\Http\Middleware\AutoAuthorizePermission`:

| Permission | Actions |
| ---------- | ------- |
| `newsletter_view` | `index` |
| `newsletter_create` | `create`, `store` |
| `newsletter_edit` | `edit`, `update` |
| `newsletter_delete` | `delete` |

Super Admin and access-level permission bypass are implemented in that middleware ([users-access-control](../users-access-control/README.md)). FormRequests `authorize()` are `true`.

### Helpers

| Method | Behavior |
| ------ | -------- |
| `channelId(): int` | `(int) auth()->user()->current_channel_id` |
| `subscriberQuery()` | `NewsletterSubscriber::query()->forChannel($this->channelId())` |

### Methods

#### `index(Request $request)`

- **Route:** `GET /admin/newsletters` (`admin.newsletters.index`)
- **AJAX (`$request->ajax()`):** Eloquent query `forChannel`, optional `q` (trim; `email` or `name` `LIKE %q%`), optional `status` exact match when non-empty. Returns `DataTables::eloquent($query)`:
  - `addIndexColumn()`
  - `name` / `source` / subscribed / unsubscribed columns use `__('translations.admin.newsletters.na')` when empty
  - `status` HTML badge: `subscribed` → `bg-success` + `translations.admin.newsletters.statuses.subscribed`; any other value → `bg-secondary` + `statuses.unsubscribed`
  - `actions` renders `admin.newsletters.actions`
  - `orderColumn('subscribed', 'subscribed_at $1')`, `orderColumn('unsubscribed', 'unsubscribed_at $1')`
  - `rawColumns(['status', 'actions'])`
- **HTML:** view `admin.newsletters.index` with compact `q`, `status` from the query string.

Index Blade DataTables: `serverSide: true`, `pageLength: 25`, `order: [[1, 'desc']]`. Column index `1` is a **hidden** `id` column in the JS `columns` array (thead has no `id` heading). Filter form `#newsletterFiltersForm` fields `q` and `status` are appended to the AJAX payload when non-empty.

#### `create()`

View `admin.newsletters.create`.

#### `store(StoreNewsletterSubscriberRequest $request, NewsletterSubscriberService $service)`

- Calls `$service->subscribe($data['email'], $data['name'] ?: null, $this->channelId(), 'admin')` (no `meta`).
- If `$data['status'] === 'unsubscribed'`, `$subscriber->update(['status' => 'unsubscribed', 'unsubscribed_at' => now()])`.
- Redirect `admin.newsletters.index` with flash `success` = `Newsletter subscriber created.`

#### `edit(int $id)`

`subscriberQuery()->findOrFail($id)` → view `admin.newsletters.edit`.

#### `update(UpdateNewsletterSubscriberRequest $request, int $id)`

Does **not** call the service. `fill`:

- `name` → `$data['name'] ?: null`
- `email` → `Str::lower(trim($data['email']))`
- `status` → `$data['status']`
- `source` → `$subscriber->source ?: 'admin'`

If `unsubscribe_token` is empty, set `(string) Str::uuid()`.

If status is `subscribed`: `subscribed_at` = existing or `now()`; `unsubscribed_at` = `null`.

Else: `unsubscribed_at` = existing or `now()` (`subscribed_at` is not cleared).

`save()`. Redirect `back()` with flash `success` = `Newsletter subscriber updated.`

#### `delete(int $id)`

`subscriberQuery()->findOrFail($id)->delete()`. Redirect index with flash `success` = `Newsletter subscriber deleted.`

Cross-channel IDs 404 because of `forChannel`.
