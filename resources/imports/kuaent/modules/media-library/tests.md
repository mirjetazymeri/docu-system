# Tests

Search of `tests/` found **no** tests for this module.

Searched for: `MediaFolder`, `MediaLibrary`, `MediaLibraryController`, `media_folders`, `admin.media`, `admin/media`, `HasMedia`, `InteractsWithMedia`, `toMediaCollection`, `gallery folder`.

Existing suites (`tests/Unit`, `tests/Feature` in `phpunit.xml`) cover other areas (catalog, checkout, pricing, bundles, configurables, theme attributes, examples). None reference the admin media library.

There is no `MediaFolder` factory and no test-only media disk configuration in `phpunit.xml` (no `MEDIA_DISK` / `FILESYSTEM_DISK` overrides there).
