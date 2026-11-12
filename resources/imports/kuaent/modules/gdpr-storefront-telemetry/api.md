# API

Base path `/api/v1`. Middleware `channel.api` + `locale.api` on all routes below. Missing channel → **400** `{ "success": false, "message": "Channel not resolved" }` on consent and event controllers.

Frontend-oriented checklist (request examples, banner rules): [FRONTEND_IMPLEMENTATION.md](../../../FRONTEND_IMPLEMENTATION.md) and root [README.md](../../../README.md) GDPR sections. This page is the backend contract.

Postman folder **Storefront Tracking** plus Account **Export Privacy Data** / **Delete Tracking Data**: `postman_collection.json`. No OpenAPI annotations on these controllers.

There are no API Resources for consent or events (except settings). Privacy export returns Eloquent JSON.

---

## Settings consumed by the storefront (not this module’s routes)

`GET /api/v1/settings` → `data.gdpr` (public schema fields only). Private (`public => false` in `config/settings.php`):

- `gdpr.analytics_anonymize_ip`
- `gdpr.analytics_retention_days`

Those two do **not** appear in `SettingResource`. Public GDPR keys include `enabled`, `cookie_banner_enabled`, banner title/text, legal URLs, `show_reject_button`, `consent_expiry_days`, `newsletter_consent_text`, `marketing_default_checked`, `analytics_tracking_enabled`, `analytics_require_consent`, `consent_version`.

`consent_expiry_days` is not applied by this app’s consent/event PHP.

---

## `GET /api/v1/storefront/consent`

**Auth:** optional Sanctum. **Identity:** query or body `visitor_id` / `session_id` if not authenticated.

**200**

```json
{
  "success": true,
  "data": {
    "consent_analytics": false,
    "consent_marketing": false,
    "policy_version": null,
    "consented_at": null,
    "withdrawn_at": null
  },
  "meta": {
    "channel_id": 1,
    "has_record": false
  }
}
```

Timestamps are `Carbon::toISOString()` when a row exists.

---

## `POST /api/v1/storefront/consent`

**Auth:** optional Sanctum. Guests must send `visitor_id` or `session_id` ([requests-validation.md](requests-validation.md)).

Body fields: `visitor_id`, `session_id`, `consent_analytics` (required boolean), `consent_marketing`, `consent_source`, `policy_version`, `preferences` (object).

**201**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "consent_analytics": true,
    "consent_marketing": false,
    "policy_version": "v1",
    "consented_at": "2026-09-02T15:00:00.000000Z",
    "withdrawn_at": null
  },
  "meta": {
    "channel_id": 1
  }
}
```

If `consent_analytics` is false, `withdrawn_at` is set to now (same as a reject write).

---

## `DELETE /api/v1/storefront/consent`

**Auth:** optional Sanctum. Same identity as GET.

**200** `{ "success": true, "message": "Consent revoked.", "data": { "id": 1, "withdrawn_at": "…" } }`

**404** `{ "success": false, "message": "Consent record not found." }`

Does not delete the row; flags false + `withdrawn_at`.

---

## `POST /api/v1/storefront/events`

**Auth:** optional Sanctum. **Throttle:** `120,1`.

Allowed `event_type`: `click`, `search`, `view`, `navigation`, `add_to_cart`, `custom`. `search` requires `search_term`.

Suggested body (root README / FRONTEND_IMPLEMENTATION): `event_name`, `entity_type`, `entity_id`, `url`, `referrer`, `visitor_id`, `session_id`, `consent`, `payload`, optional `occurred_at`.

### Stored

**201**

```json
{
  "success": true,
  "stored": true,
  "data": {
    "id": 1,
    "event_type": "click",
    "occurred_at": "2026-09-02T15:00:00.000000Z"
  },
  "meta": {
    "channel_id": 1
  }
}
```

### Not stored (accepted)

**202** `{ "success": true, "stored": false, "reason": "tracking_disabled" }`  
when `gdpr.analytics_tracking_enabled` is false.

**202** `{ "success": true, "stored": false, "reason": "consent_required" }`  
when `gdpr.enabled` and `gdpr.analytics_require_consent` are true and the resolved consent boolean is false.

FRONTEND_IMPLEMENTATION: do not retry blocked events repeatedly (client guidance; this API does not persist a retry-after).

---

## `GET /api/v1/me/privacy/export`

**Auth:** `auth:sanctum` + `verified.if-required`.

**200** `{ "success": true, "data": { "events": [ … ], "consents": [ … ] }, "meta": { "events_count": n, "consents_count": n } }`

Events: max **5000**, `user_id` = current user, `orderByDesc('occurred_at')`. Consents: all rows for that `user_id`. Full model attributes (including `ip_hash`, `user_agent`, `payload`).

Unauthenticated: Laravel Sanctum **401**. Unverified when required: **403** `email_not_verified`.

---

## `DELETE /api/v1/me/privacy/tracking`

**Auth:** same as export.

**200**

```json
{
  "success": true,
  "message": "Tracking data deleted for authenticated user.",
  "data": {
    "events_deleted": 0,
    "consents_deleted": 0
  }
}
```

Counts are query-builder `delete()` return values for `user_id` matches only.
