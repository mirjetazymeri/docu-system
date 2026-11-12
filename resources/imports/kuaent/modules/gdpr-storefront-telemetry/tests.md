# Tests

Searched `tests/` for `StorefrontConsent`, `StorefrontEvent`, `PrivacyController`, `StorefrontConsentController`, `StorefrontEventController`, `PurgeStorefrontTelemetry`, `gdpr:purge`, `storefront/consent`, `storefront/events`, `me/privacy`, `ip_hash`, and `consent_analytics`.

**No matches.** There are no Feature or Unit tests for this module.

Existing `tests/` files (catalog configurable/bundle, pricing, checkout address fields, etc.) do not cover GDPR telemetry.

## What is untested in-repo

- Consent show/store/revoke identity rules and 400/404
- Event 201 vs 202 `tracking_disabled` / `consent_required`
- Throttle `120,1`
- Privacy export limit 5000 and delete counts
- `gdpr:purge-telemetry` dry-run vs delete, `--days`, withdrawn-only consents
- Admin DataTables filters and channel scope

## Manual / collection (not PHPUnit)

- Root [README.md](../../../README.md) `php artisan route:list` snippet for these URIs
- `postman_collection.json` folder **Storefront Tracking** and Account privacy requests

Run (would be empty for this module):

```bash
php artisan test --filter=Storefront
```

Not determinable from the current repository whether CI runs extra suites outside `tests/`.
