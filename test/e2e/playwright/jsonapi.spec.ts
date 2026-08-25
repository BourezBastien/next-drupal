import { expect, test } from "@playwright/test"

/**
 * Smoke tests against a locally installed Drupal site seeded by
 * next_tests_seed (see test/e2e/README.md).
 *
 * These specs are deterministic: they assert the exact titles seeded by
 * modules/next/tests/modules/next_tests_seed, so they run on any machine
 * without Chapter Three's private database.
 */
test.describe("JSON:API", () => {
  test("serves the JSON:API index", async ({ request }) => {
    const response = await request.get("/jsonapi")
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body).toHaveProperty("data")
    expect(body.links).toHaveProperty("node--next_test_page")
  })

  test("serves the deterministic seeded pages", async ({ request }) => {
    const response = await request.get(
      "/jsonapi/node/next_test_page?fields[node--next_test_page]=title"
    )
    expect(response.status()).toBe(200)
    const body = await response.json()
    const titles = body.data.map(
      (resource: { attributes: { title: string } }) => resource.attributes.title
    )
    expect(titles).toContain("Next tests home")
    expect(titles).toContain("Next tests about")
  })

  test("resolves a seeded path via the decoupled router", async ({
    request,
  }) => {
    const response = await request.get("/router/translate-path", {
      params: { path: "/next-tests/home" },
    })
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body.entity.bundle).toBe("next_test_page")
    expect(body.label).toBe("Next tests home")
  })
})
