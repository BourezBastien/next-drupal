// getIndex() auth coverage lives in its own file so it runs on PR CI —
// resource-methods.test.ts holds live-Drupal tests that CI skips on PRs.
import { afterEach, describe, expect, jest, test } from "@jest/globals"
import { NextDrupal } from "../../src"
import { BASE_URL, spyOnDrupalFetch, spyOnFetch } from "../utils"

jest.setTimeout(10000)

afterEach(() => {
  jest.restoreAllMocks()
})

describe("getIndex() auth", () => {
  test("makes un-authenticated requests by default", async () => {
    const drupal = new NextDrupal(BASE_URL)
    const drupalFetchSpy = spyOnDrupalFetch(drupal)

    await drupal.getIndex()

    expect(drupalFetchSpy).toHaveBeenCalledWith(expect.anything(), {
      withAuth: false,
    })
  })

  test("makes authenticated requests with withAuth option", async () => {
    const drupal = new NextDrupal(BASE_URL, {
      auth: `Bearer sample-token`,
    })
    const fetchSpy = spyOnFetch()

    await drupal.getIndex(undefined, { withAuth: true })

    expect(
      (fetchSpy.mock.lastCall[1].headers as Headers).get("Authorization")
    ).toBe("Bearer sample-token")
  })
})
