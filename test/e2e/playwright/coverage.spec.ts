import { expect, test } from "@playwright/test"

/**
 * Deeper coverage of the seeded content: entity relationships through
 * JSON:API includes, the actual serving of the seeded file, the menu link
 * target and the configured front page.
 */
const mediaUuid = "00000000-0000-0000-0000-0000000000bb"

test.describe("Coverage", () => {
  test("includes the article image and term relationships", async ({
    request,
  }) => {
    const response = await request.get(
      "/jsonapi/node/next_test_article?include=next_test_image,next_test_tags"
    )
    expect(response.status()).toBe(200)
    const body = await response.json()

    const includedTypes = body.included.map(
      (resource: { type: string }) => resource.type
    )
    expect(includedTypes).toContain("media--image")
    expect(includedTypes).toContain("taxonomy_term--next_test_tags")

    // The relationship on the node points at the seeded media and term.
    const relationships = body.data[0].relationships
    expect(relationships.next_test_image.data.id).toBe(mediaUuid)
    expect(relationships.next_test_tags.data).toHaveLength(1)
  })

  test("serves the seeded image file", async ({ request }) => {
    const media = await request.get(
      `/jsonapi/media/image/${mediaUuid}?include=field_media_image`
    )
    expect(media.status()).toBe(200)
    const body = await media.json()

    const file = body.included.find(
      (resource: { type: string }) => resource.type === "file--file"
    )
    expect(file).toBeTruthy()

    const download = await request.get(file.attributes.uri.url)
    expect(download.status()).toBe(200)
    expect(download.headers()["content-type"]).toContain("image/png")
  })

  test("points the seeded menu link at the home page", async ({ request }) => {
    const response = await request.get("/jsonapi/menu_items/main")
    expect(response.status()).toBe(200)
    const body = await response.json()

    const link = body.data.find(
      (item: { attributes: { title: string } }) =>
        item.attributes.title === "Next tests home link"
    )
    expect(link).toBeTruthy()
    expect(link.attributes.url).toContain("/next-tests/home")
  })

  test("renders the configured front page at the app root", async ({
    page,
  }) => {
    // The Next.js app runs with DRUPAL_FRONT_PAGE=/next-tests/home.
    await page.goto("http://127.0.0.1:3000/")
    await expect(page.locator("[data-cy=node-title]")).toHaveText(
      "Next tests home"
    )
  })
})
