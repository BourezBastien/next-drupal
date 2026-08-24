import { translatePathFromContext } from "./translate-path"
import type { GetStaticPropsContext } from "next"
import type { AccessToken } from "../types"

/**
 * @deprecated Use the corresponding DrupalClient class method instead.
 * See https://next-drupal.org/docs/client
 */
export async function getResourceTypeFromContext(
  context: GetStaticPropsContext,
  options?: {
    accessToken?: AccessToken
    prefix?: string
  }
): Promise<string | null> {
  try {
    const response = await translatePathFromContext(context, options)

    return response?.jsonapi?.resourceName ?? null
  } catch (error) {
    return null
  }
}
