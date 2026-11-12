# Workflows

Implemented sequences only. Client banner/cache behavior is described in [FRONTEND_IMPLEMENTATION.md](../../../FRONTEND_IMPLEMENTATION.md); this file is the Laravel path.

---

## 1. Consent (show / save / revoke)

```
Resolve channel (channel.api) ── missing ──► 400 Channel not resolved
        │
        ▼
Identity: Sanctum user_id  else visitor_id  else session_id  else id=0
        │
GET  /storefront/consent ──► latest matching StorefrontConsent (or empty flags, has_record false)
        │
POST /storefront/consent
        ├─ StoreStorefrontConsentRequest (guest needs visitor_id or session_id)
        ├─ policy_version or setting gdpr.consent_version
        ├─ firstOrNew(identity); fill analytics/marketing/source/preferences
        ├─ ip_hash = sha256(ip|app.key); user_agent
        ├─ consented_at = now()
        └─ withdrawn_at = now() if !consent_analytics else null
        │
DELETE /storefront/consent
        ├─ no row ──► 404 Consent record not found.
        └─ consent_analytics=false, consent_marketing=false, withdrawn_at=now()
           (row kept)
```

`POST` with `consent_analytics: false` also stamps `withdrawn_at` (reject write). FRONTEND_IMPLEMENTATION **Reject all** also calls `DELETE` after `POST`; that is a client choice. Backend `DELETE` is the revoke path that 404s if no row exists.

Event gating uses **`consent_analytics` only**, not `consent_marketing`.

There is no login hook that copies guest consent onto `user_id`.

---

## 2. Event ingest

```
POST /storefront/events   (throttle 120/min)
        │
        ▼
StoreStorefrontEventRequest (422 if invalid; search needs search_term)
        │
        ▼
Channel required
        │
        ├─ consent := request.consent if key present
        │            else stored consent_analytics for identity (else false)
        │
        ├─ !gdpr.analytics_tracking_enabled ──► 202 stored=false tracking_disabled
        ├─ gdpr.enabled && analytics_require_consent && !consent
        │       ──► 202 stored=false consent_required
        └─ StorefrontEvent::create ──► 201 stored=true
```

If `gdpr.enabled` is false, the consent gate is skipped even when `analytics_require_consent` is true.

The row’s `consent` column is the boolean used at ingest time, not a live FK.

`gdpr.analytics_tracking_enabled` defaults to **false** in `config/settings.php`; ingest stays 202 `tracking_disabled` until that setting is true for the channel.

---

## 3. Privacy export (authenticated)

```
GET /api/v1/me/privacy/export
        │
auth:sanctum ──► 401 if missing
verified.if-required ──► 403 email_not_verified if required and unverified
        │
        ▼
events  = StorefrontEvent  where user_id = me  order occurred_at desc  limit 5000
consents = StorefrontConsent where user_id = me  order consented_at desc
        │
        ▼
JSON { data.events, data.consents, meta.*_count }
```

Not scoped by channel. Guest-only rows (`user_id` null) are omitted. Not a downloadable file.

---

## 4. Privacy delete (authenticated)

```
DELETE /api/v1/me/privacy/tracking
        │
same auth/verified as export
        │
        ▼
DELETE storefront_events  WHERE user_id = me
DELETE storefront_consents WHERE user_id = me
        │
        ▼
JSON events_deleted / consents_deleted
```

Does not revoke by `visitor_id`. Does not call the purge command. Does not delete the user account.

Admin can still delete a single event by id on the current admin channel (`DELETE /admin/storefront-events/{id}`).

---

## 5. Retention purge

```
php artisan gdpr:purge-telemetry [--days=N] [--dry-run]
        │
        ▼
for each Channel
        days = --days (min 1)  else  gdpr.analytics_retention_days (default 180, min 1)
        cutoff = now() - days
        │
        events   : occurred_at < cutoff
        consents : withdrawn_at IS NOT NULL AND withdrawn_at < cutoff
        │
        dry-run: count only
        else: delete those rows
```

**Not** on the scheduler. Consents that were never withdrawn (`withdrawn_at` null) remain. See [commands.md](commands.md).

---

## 6. Admin inspect (not a privacy workflow)

Staff with `storefront_event_view` list/filter DataTables for `channel_id = current_channel_id`. `storefront_event_delete` hard-deletes one event. Consents have no admin CRUD.
