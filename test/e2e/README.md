# End-to-end tests (deterministic, self-hosted)

Smoke specs that run against a **local** Drupal site seeded with
deterministic content — no Chapter Three database required.

## Pipeline

1. `./test/e2e/install-drupal.sh` — installs a Drupal 10.6 site in
   `.phpunit-drupal/` (composer project, gitignored), copies the `next`
   module and the `next_tests_seed` module, installs with a short-path
   sqlite database, enables all modules and creates the `e2e` Next site.
2. Serve it: `(cd .phpunit-drupal && ./vendor/bin/drush runserver 127.0.0.1:8090 &)`
3. Run the specs: `npx cypress run --project test/e2e`

The specs assert the exact titles seeded by
`modules/next/tests/modules/next_tests_seed` (`Next tests home`,
`Next tests about`, paths `/next-tests/*`), so they are reproducible on
any machine.

## Known pitfalls (all hit and solved during development)

- **Keep the sqlite path short.** Drupal rejects database names > 128
  characters; long Windows project paths exceed it. Historically a
  relative sqlite path made drush and the web server resolve two
  _different_ database files, producing impossible-looking module state.
  The script uses `%TEMP%/next-e2e.sqlite`.
- **Stale container cache.** If an install crashes mid-`drush en`, the
  cached service container can claim modules are installed while
  `core.extension` disagrees (drush answers "Already installed", the web
  500s with missing services). The script purges all `cache_*` tables
  before enabling.
- **Cypress binary.** `npx cypress install` may hang during unzip on
  Windows sandboxes; extract the downloaded zip manually into
  `%LOCALAPPDATA%/Cypress/Cache/9.7.0/Cypress/` if needed.

## Status

- [x] JSON:API index smoke
- [x] Deterministic seeded collection (titles + aliases)
- [x] Decoupled router path resolution
- [ ] Full Next.js app build + rendering specs (next step: build a starter
      against this site and extend the specs)
