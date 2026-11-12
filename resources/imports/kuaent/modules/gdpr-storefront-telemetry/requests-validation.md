# Requests and validation

Two FormRequests, both `authorize(): true`. Admin event filters and privacy endpoints have **no** FormRequest.

Failed validation is Laravel’s default JSON **422**.

---

## `App\Http\Requests\Api\V1\StoreStorefrontConsentRequest`

**File:** `app/Http/Requests/Api/V1/StoreStorefrontConsentRequest.php`  
Used by: `StorefrontConsentController::store`

### `prepareForValidation`

Trims (via `(string)` then `trim`) and merges: `visitor_id`, `session_id`, `consent_source`, `policy_version`. Missing keys become `''`.

### `rules()`

| Field | Rules |
|-------|--------|
| `visitor_id` | nullable, string, max 191 |
| `session_id` | nullable, string, max 191 |
| `consent_analytics` | required, boolean |
| `consent_marketing` | nullable, boolean |
| `consent_source` | nullable, string, max 120 |
| `policy_version` | nullable, string, max 80 |
| `preferences` | nullable, array |

No validation of `preferences.*` keys.

### `withValidator` after hook

If there is no `$this->user('sanctum')` **and** `visitor_id === ''` **and** `session_id === ''`:

- Error on `visitor_id`: `visitor_id or session_id is required for guest consent.`

Authenticated users may omit both ids.

---

## `App\Http\Requests\Api\V1\StoreStorefrontEventRequest`

**File:** `app/Http/Requests/Api/V1/StoreStorefrontEventRequest.php`  
Used by: `Api\V1\StorefrontEventController::store`

### `prepareForValidation`

| Field | Transform |
|-------|-----------|
| `event_type` | `strtolower(trim(...))` |
| `event_name`, `entity_type`, `entity_id`, `url`, `referrer`, `search_term`, `visitor_id`, `session_id` | `trim` of string input |

### `rules()`

| Field | Rules |
|-------|--------|
| `event_type` | required, string, `in:click,search,view,navigation,add_to_cart,custom` |
| `event_name` | nullable, string, max 120 |
| `entity_type` | nullable, string, max 120 |
| `entity_id` | nullable, string, max 120 |
| `url` | nullable, string, max 2048 |
| `referrer` | nullable, string, max 2048 |
| `search_term` | nullable, string, max 255 |
| `visitor_id` | nullable, string, max 191 |
| `session_id` | nullable, string, max 191 |
| `payload` | nullable, array |
| `consent` | nullable, boolean |
| `occurred_at` | nullable, date |

No guest identity requirement (unlike consent store). Empty identity makes stored-consent resolution return `false` unless `consent` is sent.

### `withValidator` after hook

If `event_type === 'search'` and `search_term === ''` (after trim):

- Error on `search_term`: `search_term is required when event_type is search.`

---

## Admin / privacy

| Endpoint | Validation |
|----------|------------|
| `GET/DELETE` consent | none (identity from request) |
| Privacy export/delete | none (user from Sanctum) |
| Admin index filters | none (empty/`q`/dates used as-is; `event_type` ignored unless in `EVENT_TYPES`) |
| Admin delete | `{id}` `findOrFail` only |
