#!/usr/bin/env bash
#
# Installs a local Drupal site for the end-to-end tests and seeds it with
# deterministic content (next_tests_seed). See test/e2e/README.md.
#
# Usage: ./test/e2e/install-drupal.sh
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
PROJECT="$ROOT/.phpunit-drupal"
DB_FILE="C:/Users/$USERNAME/AppData/Local/Temp/next-e2e.sqlite"
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
      drupal/decoupled_router drupal/subrequests drupal/simple_oauth drupal/pathauto --no-interaction -W
  )
fi

# 2. Copy the module (and the seed module to a discoverable location).
cp -R modules/next "$PROJECT/web/modules/next"
cp -R modules/next/tests/modules/next_tests_seed "$PROJECT/web/modules/next_tests_seed"

# 3. Fresh site install. NOTE: keep the database path short — Drupal rejects
# database names longer than 128 characters, and long Windows project paths
# exceed it (the historical relative-path workaround caused drush/web to use
# two different sqlite files).
cd "$PROJECT"
rm -f web/sites/default/settings.php
rm -rf web/sites/default/files
rm -f "$DB_FILE"
./vendor/bin/drush site:install standard \
  --db-url="sqlite://localhost/$DB_FILE" \
  --site-name="next-drupal e2e" --account-pass=admin -y >/dev/null

# 4. Enable modules. Guard against a stale container cache claiming modules
# are already installed while core.extension disagrees (observed when a
# previous install crashed mid-enable).
php -r '
$db = new PDO("sqlite:" . getenv("E2E_DB"));
$r = $db->query("SELECT data FROM config WHERE name='"'"'core.extension'"'"'")->fetch();
$d = unserialize($r["data"]);
$missing = [];
foreach (["jsonapi","decoupled_router","subrequests","simple_oauth","pathauto","next","next_tests_seed"] as $m) {
  if (empty($d["module"][$m])) { $missing[] = $m; }
}
foreach ($db->query("SELECT name FROM sqlite_master WHERE type='"'"'table'"'"' AND name LIKE '"'"'cache_%'"'"'")->fetchAll(PDO::FETCH_COLUMN) as $t) {
  $db->exec("DELETE FROM $t");
}
if ($missing) { echo implode(",", $missing); }
' 
E2E_DB="$DB_FILE" ./vendor/bin/drush en -y jsonapi decoupled_router subrequests simple_oauth pathauto next next_tests_seed >/dev/null
./vendor/bin/drush cr -y >/dev/null

# 5. Create a Next.js site entity (needed by the next module UI and preview).
./vendor/bin/drush php-eval 'try { Drupal\next\Entity\NextSite::create(["id" => "e2e", "label" => "E2E", "base_url" => "http://localhost:3000"])->save(); } catch (\Throwable $e) {}' >/dev/null || true

echo "Site installed. Start it with:"
echo "  (cd $PROJECT && ./vendor/bin/drush runserver 127.0.0.1:8090 &)"
echo "Then run the smoke specs:"
echo "  npx cypress run --project test/e2e"
