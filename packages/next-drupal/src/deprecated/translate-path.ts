import { buildHeaders, buildUrl, getPathFromContext } from "./utils"
import type { GetStaticPropsContext } from "next"
import type { AccessToken, DrupalTranslatedPath } from "../types"

/**
 * @deprecated Use the corresponding DrupalClient class method instead.
 * See https://next-drupal.org/docs/client
 */
export async function translatePath(
  path: string,
  options?: {
    accessToken?: AccessToken
  }
): Promise<DrupalTranslatedPath | null> {
  const url = buildUrl("/router/translate-path", {
    path,
  })

  const response = await fetch(url.toString(), {
    headers: await buildHeaders(options),
  })

  if (!response.ok) {
    return null
  }

  const json = await response.json()

  return json
}

/**
 * @deprecated Use the corresponding DrupalClient class method instead.
 * See https://next-drupal.org/docs/client
 */
export async function translatePathFromContext(
  context: GetStaticPropsContext,
  options?: {
    accessToken?: AccessToken
    prefix?: string
  }
): Promise<DrupalTranslatedPath | null> {
  options = {
    prefix: "",
    ...options,
  }
  const path = getPathFromContext(context, options.prefix)

  const response = await translatePath(path, {
    accessToken: options.accessToken,
  })

  return response
}
