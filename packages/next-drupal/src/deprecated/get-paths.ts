import { getResourceCollection } from "./get-resource-collection"
import type { GetStaticPathsContext, GetStaticPathsResult } from "next"
import type {
  AccessToken,
  JsonApiParams,
  JsonApiResource,
  JsonApiResourceWithPath,
  Locale,
} from "../types"

/**
 * @deprecated Use the corresponding DrupalClient class method instead.
 * See https://next-drupal.org/docs/client
 */
export async function getPathsFromContext(
  types: string | string[],
  context: GetStaticPathsContext,
  options: {
    params?: JsonApiParams
    accessToken?: AccessToken
  } = {}
): Promise<GetStaticPathsResult["paths"]> {
  if (typeof types === "string") {
    types = [types]
  }

  const paths = await Promise.all(
    types.map(async (type) => {
      // Use sparse fieldset to expand max size.
      options.params = {
        [`fields[${type}]`]: "path",
        ...options?.params,
      }

      // const paths = await Promise.all(
      //   context.locales.map(async (locale) => {
      //     const resources = await getResourceCollection(type, {
      //       deserialize: true,
      //       locale,
      //       defaultLocale: context.defaultLocale,
      //       ...options,
      //     })

      //     return buildPathsFromResources(resources, locale)
      //   })
      // )

      // return paths.flat()

      // Handle localized path aliases
      if (!context.locales?.length) {
        const resources = await getResourceCollection(type, {
          deserialize: true,
          ...options,
        })

        return buildPathsFromResources(resources)
      }

      const paths = await Promise.all(
        context.locales.map(async (locale) => {
          const resources = await getResourceCollection(type, {
            deserialize: true,
            locale,
            defaultLocale: context.defaultLocale,
            ...options,
          })

          return buildPathsFromResources(resources, locale)
        })
      )

      return paths.flat()
    })
  )

  return paths.flat()
}

function buildPathsFromResources(
  resources: JsonApiResource[],
  locale?: Locale
) {
  return resources?.flatMap((resource) => {
    // The resources are fetched with their path field.
    const resourcePath = (resource as JsonApiResourceWithPath).path
    const slug =
      resourcePath?.alias === process.env.DRUPAL_FRONT_PAGE
        ? "/"
        : resourcePath?.alias

    const path: {
      params: { slug: string[] }
      locale?: Locale
    } = {
      params: {
        slug: `${slug?.replace(/^\/|\/$/g, "")}`.split("/"),
      },
    }

    if (locale) {
      path.locale = locale
    }

    return path
  })
}
