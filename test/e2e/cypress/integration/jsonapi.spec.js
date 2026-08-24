/// <reference types="cypress" />

/**
 * Smoke tests against a locally installed Drupal site seeded by
 * next_tests_seed (see test/e2e/README.md).
 *
 * These specs are deterministic: they assert the exact titles seeded by
 * modules/next/tests/modules/next_tests_seed, so they run on any machine
 * without Chapter Three's private database.
 */
context("JSON:API", () => {
  it("serves the JSON:API index", () => {
    cy.request("/jsonapi").then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body).to.have.property("data")
      expect(response.body.links).to.have.property("node--next_test_page")
    })
  })

  it("serves the deterministic seeded pages", () => {
    cy.request(
      "/jsonapi/node/next_test_page?fields[node--next_test_page]=title"
    ).then((response) => {
      expect(response.status).to.eq(200)
      const titles = response.body.data.map(
        (resource) => resource.attributes.title
      )
      expect(titles).to.include("Next tests home")
      expect(titles).to.include("Next tests about")
    })
  })

  it("resolves a seeded path via the decoupled router", () => {
    cy.request({
      url: "/router/translate-path",
      qs: { path: "/next-tests/home" },
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status).to.eq(200)
      expect(response.body.entity.bundle).to.eq("next_test_page")
      expect(response.body.label).to.eq("Next tests home")
    })
  })
})
