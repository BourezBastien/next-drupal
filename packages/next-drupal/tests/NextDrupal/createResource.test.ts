import { afterEach, describe, expect, jest, test } from "@jest/globals"
import { NextDrupal } from "../../src"
import { BASE_URL, mocks, spyOnFetch } from "../utils"
import type { DrupalMedia } from "../../src"

jest.setTimeout(10000)

afterEach(() => {
  jest.restoreAllMocks()
})

describe("createResource()", () => {
  test("serializes relationship meta in the request body", async () => {
    const drupal = new NextDrupal(BASE_URL, {
      auth: mocks.auth.basicAuth,
    })
    const fetchSpy = spyOnFetch({
      responseBody: mocks.resources.mediaImage,
    })

    await drupal.createResource<DrupalMedia>(
      "media--image",
      {
        data: {
          attributes: {
            name: "TEST Media image",
          },
          relationships: {
            field_media_image: {
              data: {
                type: "file--file",
                id: "641fc6a4-276d-43e9-abbd-1e51bc28ddf9",
                meta: {
                  alt: "Accessible description of the image",
                },
              },
            },
          },
        },
      },
      {
        deserialize: false,
      }
    )

    expect(fetchSpy).toHaveBeenCalledTimes(1)

    const body = JSON.parse(fetchSpy.mock.lastCall[1].body as string)
    expect(body).toEqual({
      data: {
        type: "media--image",
        attributes: {
          name: "TEST Media image",
        },
        relationships: {
          field_media_image: {
            data: {
              type: "file--file",
              id: "641fc6a4-276d-43e9-abbd-1e51bc28ddf9",
              meta: {
                alt: "Accessible description of the image",
              },
            },
          },
        },
      },
    })
  })
})
