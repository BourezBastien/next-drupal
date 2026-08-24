import type { JsonApiParams } from "./options"

export type NextDrupalBaseOptions = {
  /**
   * A long-lived access token you can set for the client.
   *
   * * **Default value**: `null`
   * * **Required**: *No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#accesstoken)
   */
  accessToken?: AccessToken

  /**
   * Set the JSON:API prefix.
   *
   * * **Default value**: `/jsonapi`
   * * **Required**: *No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#apiprefix)
   */
  apiPrefix?: string

  /**
   * Override the default auth. You can use this to implement your own authentication mechanism.
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#auth)
   */
  auth?: NextDrupalAuth

  /**
   * Set debug to true to enable debug messages.
   *
   * * **Default value**: `false`
   * * **Required**: *No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#debug)
   */
  debug?: boolean

  /**
   * Override the default fetcher. Use this to add your own fetcher ex. axios.
   *
   * * **Default value**: `fetch`
   * * **Required**: *No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#fetcher)
   */
  fetcher?: Fetcher

  /**
   * Set the default frontPage.
   *
   * Pass a string for a single front page, or a record keyed by locale for
   * multilingual sites (`{ fr: "/accueil", en: "/home" }`). With a record,
   * the `default` key is the fallback for locales without their own entry.
   *
   * * **Default value**: `/home`
   * * **Required**: *No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#frontpage)
   */
  frontPage?: string | Record<string, string>

  /**
   * Set custom headers for the fetcher.
   *
   * * **Default value**: `{ "Content-Type": "application/vnd.api+json", Accept: "application/vnd.api+json" }`
   * * **Required**: *No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#headers)
   */
  headers?: HeadersInit

  /**
   * Override the default logger. You can use this to send logs to a third-party service.
   *
   * * **Default value**: `console`
   * * **Required**: *No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#logger)
   */
  logger?: Logger

  /**
   * Set whether the client should use authenticated requests by default.
   *
   * * **Default value**: `true`
   * * **Required**: **No*
   *
   * [Documentation](https://next-drupal.org/docs/client/configuration#withauth)
   */
  withAuth?: boolean
}

export type NextDrupalAuth =
  | NextDrupalAuthAccessToken
  | NextDrupalAuthClientIdSecret
  | NextDrupalAuthUsernamePassword
  | (() => string)
  | string

export type NextDrupalAuthAccessToken = AccessToken

export interface NextDrupalAuthClientIdSecret {
  clientId: string
  clientSecret: string
  url?: string
  scope?: string
}

export interface NextDrupalAuthUsernamePassword {
  username: string
  password: string
}

export interface AccessToken {
  token_type: string
  access_token: string
  expires_in: number
  refresh_token?: string
}

export type AccessTokenScope = string

export type Fetcher = WindowOrWorkerGlobalScope["fetch"]

export interface Logger {
  log(message: string | Error): void

  debug(message: string | Error): void

  warn(message: string | Error): void

  error(message: string | Error): void
}

export type EndpointSearchParams =
  | string
  | Record<string, string>
  | URLSearchParams
  | JsonApiParams
