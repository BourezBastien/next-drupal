import { expect, request, test } from "@playwright/test"

/**
 * Rendering smoke tests against the local Next.js app (test/e2e/next-app)
 * backed by the seeded Drupal site.
 */
// The Next.js app runs on its own port; the Drupal site is the baseURL.
const app = "http://127.0.0.1:3000"

test.describe("Rendering", () => {
  test("renders the seeded home page", async ({ page }) => {
    await page.goto(`${app}/next-tests/home`)
    await expect(page.locator("[data-cy=node-title]")).toHaveText(
      "Next tests home"
    )
    await expect(page.locator("[data-cy=node-type]")).toHaveText(
      "node--next_test_page"
    )
  })

  test("renders the seeded about page", async ({ page }) => {
    await page.goto(`${app}/next-tests/about`)
    await expect(page.locator("[data-cy=node-title]")).toHaveText(
      "Next tests about"
    )
  })

  test("serves a 404 for unknown paths", async () => {
    const context = await request.newContext({ baseURL: app })
    const response = await context.get("/does-not-exist")
    expect(response.status()).toBe(404)
    await context.dispose()
  })
})
