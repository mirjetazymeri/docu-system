# Newsletter and contact — Requests and validation

Module README: [README.md](README.md)

All FormRequests below return `true` from `authorize()`. API auth is “public”; admin authorization is `check.permission` on the controller, not these classes.

Laravel 422 JSON (API) / redirect with errors (admin web) is framework default. There are no custom `messages()` / `attributes()` on these classes.

---

## `App\Http\Requests\Api\V1\SubscribeNewsletterRequest`

Source: `app/Http/Requests/Api/V1/SubscribeNewsletterRequest.php`

### `prepareForValidation`

Merges:

- `email` → `Str::lower(trim((string) input email))`
- `name` → `trim((string) input name)` (missing name becomes `''`, not `null`)

### `rules()`

| Field | Rules |
| ----- | ----- |
| `name` | nullable, string, max 191 |
| `email` | required, string, `email`, max 191 |

`email` is Laravel’s default `email` rule (not `email:rfc` / `email:rfc,dns`).

---

## `App\Http\Requests\Api\V1\UnsubscribeNewsletterRequest`

Source: `app/Http/Requests/Api/V1/UnsubscribeNewsletterRequest.php`

### `prepareForValidation`

- `email` → if `filled('email')` then `Str::lower(trim(...))`, else `null`
- `token` → `trim((string) input token)` (missing token becomes `''`)

### `rules()`

| Field | Rules |
| ----- | ----- |
| `email` | nullable, `required_without:token`, string, `email`, max 191 |
| `token` | nullable, `required_without:email`, string, max 64 |

Both may be present; the controller then prefers token via `$request->filled('token')`. Empty string token is not `filled`.

---

## `App\Http\Requests\Api\V1\ContactFormRequest`

Source: `app/Http/Requests/Api/V1/ContactFormRequest.php`

### `prepareForValidation`

Trims `name`, `email`, `subject`, `message`. `phone` → trim if filled, else `null`.

### `rules()`

| Field | Rules |
| ----- | ----- |
| `name` | required, string, max 100 |
| `email` | required, `email:rfc`, max 191 |
| `subject` | required, string, max 255 |
| `message` | required, string, min 10, max 5000 |
| `phone` | nullable, string, max 30 |

`phone` is never forwarded after validation ([controllers.md](controllers.md)).

---

## `App\Http\Requests\Admin\NewsletterSubscriber\StoreNewsletterSubscriberRequest`

Source: `app/Http/Requests/Admin/NewsletterSubscriber/StoreNewsletterSubscriberRequest.php`

### `prepareForValidation`

- `email` → `Str::lower(trim(...))`
- `name` → `trim(...)`
- `status` → `input('status', 'subscribed')`

### `rules()`

`$channelId = (int) auth()->user()->current_channel_id`.

| Field | Rules |
| ----- | ----- |
| `name` | nullable, string, max 191 |
| `email` | required, string, `email`, max 191, `Rule::unique('newsletter_subscribers', 'email')->where(channel_id = $channelId)` |
| `status` | required, `Rule::in(['subscribed', 'unsubscribed'])` |

Unlike the API, a duplicate email on the same channel fails unique validation instead of upserting.

---

## `App\Http\Requests\Admin\NewsletterSubscriber\UpdateNewsletterSubscriberRequest`

Source: `app/Http/Requests/Admin/NewsletterSubscriber/UpdateNewsletterSubscriberRequest.php`

Extends `StoreNewsletterSubscriberRequest` (inherits `authorize()` and `prepareForValidation`). Overrides `rules()` only.

Email unique rule is the same channel `where`, plus `->ignore((int) $this->route('id'))`. Route parameter name is `id` (`newsletters/{id}` / `newsletters/edit/{id}`).
