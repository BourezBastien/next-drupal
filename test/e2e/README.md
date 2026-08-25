# End-to-end tests (deterministic, self-hosted)

Smoke specs that run against a **local** Drupal site seeded with
deterministic content — no Chapter Three database required.

## Pipeline

1. `./test/e2e/install-drupal.sh` — installs a Drupal 10.6 site in
   `.phpunit-drupal/` (composer project, gitignored), copies the `next`
   module and the `next_tests_seed` module, installs with a short-path
   sqlite database, enables all modules and creates the `e2e` Next site.
2. Serve it: `(cd .phpunit-drupal && ./vendor/bin/drush runserver 127.0.0.1:8090 &)`
3. Build and serve the Next.js app (test/e2e/next-app, a workspace using the local next-drupal):
   ```sh
   cd test/e2e/next-app
   DRUPAL_BASE_URL=http://127.0.0.1:8090 npx next build
   DRUPAL_BASE_URL=http://127.0.0.1:8090 npx next start -p 3000 &
   ```
4. Run the specs: `npx cypress run --project test/e2e` (JSON:API specs target
   the Drupal site on :8090, rendering specs target the Next.js app on :3000).

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

## Playwright (parallel suite)

The same deterministic pipeline is covered by a Playwright suite mirroring
the Cypress specs (same seeded content, same assertions):

```
cd test/e2e
npm install                       # installs @playwright/test locally
npx playwright install chromium   # or use the system browser, see below
npx playwright test               # Drupal on :8090, Next.js app on :3000
```

The `test/e2e/package.json` is intentionally independent from the monorepo
workspaces so the root `yarn.lock` stays untouched.

### Sandboxed environments

When the Playwright browser download is blocked, run the browser tests on
the system Chromium (Edge on Windows):

```
PLAYWRIGHT_CHANNEL=msedge npx playwright test
```

## Operational pitfalls (updated)

- **Never recopy `modules/next` into `.phpunit-drupal/web/modules` while the
  PHP server is running.** On Windows the `rm -rf` of the target silently
  fails on locked files and the copy nests (`web/modules/next/next`), which
  breaks the site with `AssertionError` on `next/next/next.info.yml`. Always
  stop the PHP server first (`taskkill /F /IM php.exe`), then remove, copy,
  purge caches and restart.
- After reinstalling the site or replacing module code, a full reset may be
  needed: purge the `cache_*` tables of `web/e2e.sqlite`, delete
  `web/sites/default/files/php` (container dump), `drush cr`, restart the
  server.
