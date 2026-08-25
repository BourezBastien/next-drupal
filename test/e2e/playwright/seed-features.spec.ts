import { expect, test } from "@playwright/test"

/**
 * Revision, media, menu and article smoke tests against the seeded Drupal
 * site (next_tests_seed).
 */

async function homeUuid(request: import("@playwright/test").APIRequestContext) {
  const response = await request.get(
    "/jsonapi/node/next_test_page?filter%5Btitle%5D=Next%20tests%20home&fields%5Bnode--next_test_page%5D=drupal_internal__vid"
  )
  const body = await response.json()
  expect(body.data).toHaveLength(1)
  return body.data[0].id as string
}

test.describe("Seed features", () => {
  test("serves the first revision via resourceVersion=id:1", async ({
    request,
  }) => {
    const uuid = await homeUuid(request)
    const response = await request.get(
      `/jsonapi/node/next_test_page/${uuid}?resourceVersion=id%3A1&fields%5Bnode--next_test_page%5D=drupal_internal__vid`
    )
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body.data.attributes.drupal_internal__vid).toBe(1)
  })

  test("serves a newer latest revision", async ({ request }) => {
    const uuid = await homeUuid(request)
    const response = await request.get(
      `/jsonapi/node/next_test_page/${uuid}?fields%5Bnode--next_test_page%5D=drupal_internal__vid`
    )
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body.data.attributes.drupal_internal__vid).toBeGreaterThan(1)
  })

  test("exposes the seeded image media with alt text", async ({ request }) => {
    const response = await request.get(
      "/jsonapi/media/image/00000000-0000-0000-0000-0000000000bb"
    )
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body.data.attributes.name).toBe("Next tests image")
    // The alt text lives on the file relationship meta.
    expect(body.data.relationships.field_media_image.data.meta.alt).toBe(
      "Deterministic next-drupal test image"
    )
  })

  test("exposes the seeded menu link", async ({ request }) => {
    const response = await request.get("/jsonapi/menu_items/main")
    expect(response.status()).toBe(200)
    const body = await response.json()
    const titles = body.data.map(
      (item: { attributes: { title: string } }) => item.attributes.title
    )
    expect(titles).toContain("Next tests home link")
  })

  test("exposes the seeded article with its alias", async ({ request }) => {
    const response = await request.get(
      "/jsonapi/node/next_test_article?filter[status]=1"
    )
    expect(response.status()).toBe(200)
    const body = await response.json()
    expect(body.data).toHaveLength(1)
    const attributes = body.data[0].attributes
    expect(attributes.title).toBe("Next tests article")
    expect(attributes.path.alias).toBe("/next-tests/article")
  })

  test("references the seeded taxonomy term from the article", async ({
    request,
  }) => {
    const response = await request.get("/jsonapi/taxonomy_term/next_test_tags")
    expect(response.status()).toBe(200)
    const body = await response.json()
    const names = body.data.map(
      (term: { attributes: { name: string } }) => term.attributes.name
    )
    expect(names).toContain("Next tests tag")
  })

  test("renders the seeded article", async ({ page }) => {
    // The Next.js app runs on its own port; the Drupal site is the baseURL.
    await page.goto("http://127.0.0.1:3000/next-tests/article")
    await expect(page.locator("[data-cy=node-title]")).toContainText(
      "Next tests article"
    )
  })
})
