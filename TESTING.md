# Testing

## next-drupal (NPM package)

To run the tests for `next-drupal`, run:

```
yarn test
```

Most tests require a running Drupal instance with credentials (see
below), but the pure unit tests can run anywhere without a live Drupal:

```
cd packages/next-drupal
DRUPAL_BASE_URL=http://localhost \
DRUPAL_CLIENT_ID=test \
DRUPAL_CLIENT_SECRET=test \
npx jest tests/NextDrupalBase tests/Logger tests/DrupalMenuTree tests/draft --coverage=false
```

Without these environment variables the test bootstrap fails with
`The 'baseUrl' param is required` — copy
`packages/next-drupal/.env.example` to `packages/next-drupal/.env` or
export the variables before running. (#533)

The full suite additionally needs a live Drupal site (OAuth consumer,
seeded umami-style content) provisioned by the maintainers; roughly 86
network tests fail without it. Treat that as the baseline when running
locally, not as regressions.

## Next module (PHP)

Tests for the `next` module use PHPUnit.

1. Set up a Drupal 10 test project (see `test/e2e/install-drupal.sh` for
   a working recipe, or `.agents/skills/next-drupal-dev/SKILL.md`).
2. Copy `modules/next` into the project's `web/modules/`.
3. Run PHPUnit against it:

```
SIMPLETEST_DB="sqlite://localhost/:memory:" \
SIMPLETEST_BASE_URL="http://127.0.0.1:8080" \
../vendor/bin/phpunit -c core modules/next
```

Static analysis: `phpstan analyse` (level 5, `phpstan.neon`) and
`phpcs` with `modules/next/phpcs.xml`.

## End-to-end tests

The deterministic, self-hosted pipeline lives in `test/e2e` — see
`test/e2e/README.md`. It installs a local Drupal site seeded by
`next_tests_seed` and runs Cypress specs against both the JSON:API and
a Next.js app, with no external database required:

```
./test/e2e/install-drupal.sh
(cd .phpunit-drupal && ./vendor/bin/drush runserver 127.0.0.1:8090 &)
cd test/e2e/next-app && DRUPAL_BASE_URL=http://127.0.0.1:8090 npx next build && DRUPAL_BASE_URL=http://127.0.0.1:8090 npx next start &
npx cypress run --project test/e2e
```

The legacy example suites under `examples/*/cypress` assert content
that only exists in Chapter Three's private test database; obtaining a
copy is tracked in `docs/PLAN.md` (ready-to-post request in
`docs/SLACK-DB-REQUEST.md`).

## Playwright

The deterministic pipeline also ships a Playwright suite mirroring the
Cypress specs (13 tests each). It lives in `test/e2e` with its own
`package.json` (independent from the root workspaces):

```
cd test/e2e
npm install
npx playwright install chromium
npx playwright test
```

When browser downloads are blocked, use the system browser channel:
`PLAYWRIGHT_CHANNEL=msedge npx playwright test`.
