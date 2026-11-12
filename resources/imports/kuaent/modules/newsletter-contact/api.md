# Newsletter and contact — Storefront API

Module README: [README.md](README.md) · Validation: [requests-validation.md](requests-validation.md) · Controllers: [controllers.md](controllers.md)

Shared v1 headers/query (group middleware, not unique to this module): `X-Channel` or `?channel=` (channel **code**), plus locale headers used by `locale.api`. Newsletter subscribe/unsubscribe **require** a resolved `currentChannel`. Contact does not read the channel in the controller.

There are no API Resource classes; responses are inline arrays from the controllers.

Client notes also exist in `postman_collection.json` folder **Newsletter and Contact**. Source of truth is the PHP below. The collection’s subscribe 201 sample uses a placeholder `unsubscribe_token` of `example-value`; live tokens are UUIDs from `Str::uuid()`.

---

## `POST /api/v1/newsletter/subscribe`

### Purpose

Create or reactivate a subscriber for the current API channel (`NewsletterSubscriberService::subscribe`).

### Authentication

Public. `SubscribeNewsletterRequest::authorize()` is `true`.

### Request

**JSON body**

| Field | Required | Notes |
| ----- | -------- | ----- |
| `email` | yes | lowercased/trimmed in FormRequest; max 191; rule `email` |
| `name` | no | trimmed; max 191 |

### Response

HTTP **201**:

| Path | Meaning |
| ---- | ------- |
| `success` | `true` |
| `message` | `Newsletter subscription saved.` |
| `data.id` | Subscriber id |
| `data.email` | Stored email |
| `data.status` | After save: `subscribed` |
| `data.unsubscribe_token` | Token for later unsubscribe (returned on every successful subscribe, including re-subscribe of an existing row) |
| `meta.channel_id` | `(int)` resolved channel id |

### Possible errors

| Status | Condition |
| ------ | --------- |
| 400 | `app('currentChannel')` falsy — `{ "success": false, "message": "Channel not resolved" }` |
| 422 | Validation failure |

### Side effects

- Insert or update `newsletter_subscribers` for `(channel_id, email)`.
- `meta` merge includes `ip` and `user_agent`.
- No mail.

---

## `POST /api/v1/newsletter/unsubscribe`

### Purpose

Set `status` to `unsubscribed`. Lookup by token **or** by email within the current channel.

### Authentication

Public.

### Request

**JSON body** — at least one of `email` / `token` (`required_without` each other):

| Field | Required | Notes |
| ----- | -------- | ----- |
| `email` | unless `token` | lowercased when filled; max 191 |
| `token` | unless `email` | max 64; if filled, controller ignores email for lookup |

### Response

HTTP **200**:

| Path | Meaning |
| ---- | ------- |
| `success` | `true` |
| `message` | `Newsletter subscription removed.` |
| `data.id` / `data.email` / `data.status` | After `markUnsubscribed` (`status` is `unsubscribed`) |
| `meta.channel_id` | `(int) $subscriber->channel_id` |

### Possible errors

| Status | Condition |
| ------ | --------- |
| 400 | Channel not resolved (checked **before** token/email lookup) |
| 404 | `{ "success": false, "message": "Subscriber not found." }` |
| 422 | Validation failure (neither field, invalid email, etc.) |

Token unsubscribe can mark a row whose `channel_id` differs from the request channel; the 400 check still requires some channel to resolve.

---

## `POST /api/v1/contact`

### Purpose

Forward a message to the admin mailbox via `MailService::forwardContactForm`.

CMS widgets of type `contact_form` set `config.submit_endpoint` to `url('/api/v1/contact')` ([cms-pages-widgets](../cms-pages-widgets/README.md)). That is a consumer, not a second endpoint.

### Authentication

Public. Throttled `throttle:10,1`.

### Request

**JSON body**

| Field | Required | Notes |
| ----- | -------- | ----- |
| `name` | yes | max 100 |
| `email` | yes | `email:rfc`, max 191 |
| `subject` | yes | max 255 |
| `message` | yes | min 10, max 5000 |
| `phone` | no | max 30; **not** included in the mailable |

### Response

HTTP **200**:

| Path | Meaning |
| ---- | ------- |
| `success` | `true` |
| `message` | `Your message has been sent. We will get back to you shortly.` |

This body is returned after `forwardContactForm` returns, including when the setting toggle skips send or `Mail::send` fails internally ([notifications.md](notifications.md)).

### Possible errors

| Status | Condition |
| ------ | --------- |
| 422 | Validation failure |
| 429 | Throttle `10,1` exceeded |

There is no 400 channel check on this action.

### Side effects

- May send `ContactFormMail` (sync).
- No database insert.
