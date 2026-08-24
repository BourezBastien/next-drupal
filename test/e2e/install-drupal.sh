#!/usr/bin/env bash
#
# Installs a local Drupal site for the end-to-end tests and seeds it with
# deterministic content (next_tests_seed). See test/e2e/README.md.
#
# Usage: ./test/e2e/install-drupal.sh
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PROJECT="$ROOT/.phpunit-drupal"
SITE_URL="http://127.0.0.1:8090"

cd "$ROOT"

# 1. Drupal project with dev dependencies (skipped when already present).
if [ ! -f "$PROJECT/vendor/bin/drush" ]; then
  mkdir -p "$PROJECT"
  ( cd "$PROJECT"
    COMPOSER_MEMORY_LIMIT=-1 composer create-project drupal/recommended-project:10.6.x . --no-interaction --no-install
    composer config minimum-stability dev
    composer config prefer-stable true
    composer config repositories.0 composer https://packages.drupal.org/8
    composer config audit.block-insecure false
    COMPOSER_MEMORY_LIMIT=-1 composer require "drupal/core-dev-pinned:10.6.x" drupal/coder drush/drush \
      drupal/decoupled_router drupal/subrequests drupal/simple_oauth drupal/pathauto \
      drupal/jsonapi_menu_items --no-interaction -W
  )
fi

# 2. Copy the module (and the seed module to a discoverable location).
cp -R modules/next "$PROJECT/web/modules/next"
cp -R modules/next/tests/modules/next_tests_seed "$PROJECT/web/modules/next_tests_seed"

# 3. Fresh site install. NOTE: Drupal rejects database names longer than
# 128 characters, so keep the sqlite path short and project-relative. A
# relative "e2e.sqlite" resolves against the drupal working directory.
cd "$PROJECT"
rm -f web/sites/default/settings.php e2e.sqlite web/e2e.sqlite
rm -rf web/sites/default/files
./vendor/bin/drush site:install standard \
  --db-url="sqlite://localhost/e2e.sqlite" \
  --site-name="next-drupal e2e" --account-pass=admin -y 2>&1 | grep -E "Installation complete" || {
    echo "Install failed" >&2
    exit 1
  }

# 4. Enable modules and rebuild.
./vendor/bin/drush en -y jsonapi jsonapi_resources jsonapi_menu_items \
  decoupled_router subrequests simple_oauth pathauto next next_tests_seed 2>&1 | grep -E "\[success\]" | tail -2
./vendor/bin/drush cr -y >/dev/null

# 5. A Next.js site entity for the next module configuration.
./vendor/bin/drush php-eval 'try { Drupal\next\Entity\NextSite::create(["id" => "e2e", "label" => "E2E", "base_url" => "http://localhost:3000"])->save(); } catch (\Throwable $e) {}' >/dev/null || true

echo "Site installed (sqlite: $PROJECT/e2e.sqlite). Start it with:"
echo "  (cd $PROJECT && ./vendor/bin/drush runserver 127.0.0.1:8090 &)"
echo "Then build the app and run the specs:"
echo "  cd test/e2e/next-app && DRUPAL_BASE_URL=$SITE_URL npx next build && DRUPAL_BASE_URL=$SITE_URL npx next start &"
echo "  npx cypress run --project test/e2e"
