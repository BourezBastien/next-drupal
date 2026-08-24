import { describe, expect, test } from "@jest/globals"
import { isAccessTokenAuth, isBasicAuth, isClientIdSecretAuth } from "../../src"
import { BASE_URL, mocks, spyOnFetch, spyOnFetchOnce } from "../utils"

const { accessToken, basicAuth, clientIdSecret } = mocks.auth

describe("isBasicAuth", () => {
  test("returns false if username is undefined", () => {
    expect(
      isBasicAuth(
        // @ts-expect-error
        { password: basicAuth.password }
      )
    ).toBe(false)
  })

  test("returns false if password is undefined", () => {
    expect(
      isBasicAuth(
        // @ts-expect-error
        { username: basicAuth.username }
      )
    ).toBe(false)
  })

  test("returns true if username and password are given", () => {
    expect(isBasicAuth(basicAuth)).toBe(true)
  })
})

describe("isAccessTokenAuth", () => {
  test("returns false if access_token is undefined", () => {
    expect(
      isAccessTokenAuth(
        // @ts-expect-error
        { token_type: accessToken.token_type }
      )
    ).toBe(false)
  })

  test("returns false if token_type is undefined", () => {
    expect(
      isAccessTokenAuth(
        // @ts-expect-error
        { access_token: accessToken.access_token }
      )
    ).toBe(false)
  })

  test("returns true if access_token and token_type are given", () => {
    expect(isAccessTokenAuth(accessToken)).toBe(true)
  })
})

describe("isClientIdSecretAuth", () => {
  test("returns false if clientId is undefined", () => {
    expect(
      isClientIdSecretAuth(
        // @ts-expect-error
        { clientSecret: clientIdSecret.clientSecret }
      )
    ).toBe(false)
  })

  test("returns false if clientSecret is undefined", () => {
    expect(
      isClientIdSecretAuth(
        // @ts-expect-error
        { clientId: clientIdSecret.clientId }
      )
    ).toBe(false)
  })

  test("returns true if clientId and clientSecret are given", () => {
    expect(isClientIdSecretAuth(clientIdSecret)).toBe(true)
  })
})

describe("getAccessToken with the password grant", () => {
  test("fetches a user token with grant_type=password", async () => {
    const { NextDrupalBase } = await import("../../src")
    const drupal = new NextDrupalBase(BASE_URL, {
      auth: {
        clientId: "client-id",
        clientSecret: "client-secret",
      },
    })

    const fetchSpy = spyOnFetch({
      responseBody: {
        access_token: "user-token",
        token_type: "Bearer",
        expires_in: 300,
      },
    })

    const token = await drupal.getAccessToken({
      username: "editor",
      password: "secret",
    })

    expect(token.access_token).toBe("user-token")

    const init = fetchSpy.mock.calls[0][1]
    expect(init.body.toString()).toContain("grant_type=password")
    expect(init.body.toString()).toContain("username=editor")
    expect(init.body.toString()).toContain("password=secret")
  })

  test("does not cache user tokens across requests", async () => {
    const { NextDrupalBase } = await import("../../src")
    const drupal = new NextDrupalBase(BASE_URL, {
      auth: {
        clientId: "client-id",
        clientSecret: "client-secret",
      },
    })

    // A response body can only be read once: return a fresh one per call.
    const tokenResponse = () => ({
      access_token: "user-token",
      token_type: "Bearer",
      expires_in: 300,
    })
    const first = spyOnFetch({ responseBody: tokenResponse() })
    spyOnFetchOnce({ responseBody: tokenResponse() })
    // The spy is shared across tests in this file: count only our calls.
    first.mockClear()

    await drupal.getAccessToken({ username: "editor", password: "secret" })
    await drupal.getAccessToken({ username: "editor", password: "secret" })

    expect(first).toBeCalledTimes(2)
  })

  test("throws without configured client credentials", async () => {
    const { NextDrupalBase } = await import("../../src")
    const drupal = new NextDrupalBase(BASE_URL)

    await expect(
      drupal.getAccessToken({ username: "editor", password: "secret" })
    ).rejects.toThrow("password grant requires the client")
  })
})
