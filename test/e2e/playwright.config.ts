import { defineConfig } from "@playwright/test"

/**
 * Playwright suite for the deterministic end-to-end pipeline.
 *
 * Mirrors the Cypress specs in test/e2e/cypress: same seeded content, same
 * assertions. Run it against the locally installed site (see
 * test/e2e/README.md):
 *
 *   npx playwright install chromium
 *   npx playwright test --config test/e2e/playwright.config.ts
 */
export default defineConfig({
  testDir: "./playwright",
  timeout: 30_000,
  // Launching the system browser channel can be flaky on shared machines.
  retries: process.env.CI ? 0 : 1,
  use: {
    // The Drupal site. The Next.js app runs on port 3000 and is addressed
    // with absolute URLs from the rendering specs.
    baseURL: process.env.DRUPAL_BASE_URL ?? "http://127.0.0.1:8090",
    // The sandboxed CI cannot download Playwright browsers: fall back to
    // the system Edge (chromium) channel when told to.
    channel: process.env.PLAYWRIGHT_CHANNEL || undefined,
  },
  reporter: process.env.CI ? "list" : "list",
})
