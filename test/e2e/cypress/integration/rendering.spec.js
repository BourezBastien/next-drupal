/// <reference types="cypress" />

/**
 * Rendering smoke tests against the local Next.js app (test/e2e/next-app)
 * backed by the seeded Drupal site.
 */
context("Rendering", () => {
  // The Next.js app runs on its own port; the Drupal site is the baseUrl.
  const app = "http://127.0.0.1:3000"

  it("renders the seeded home page", () => {
    cy.visit(`${app}/next-tests/home`)
    cy.get("[data-cy=node-title]").should("have.text", "Next tests home")
    cy.get("[data-cy=node-type]").should("have.text", "node--next_test_page")
  })

  it("renders the seeded about page", () => {
    cy.visit(`${app}/next-tests/about`)
    cy.get("[data-cy=node-title]").should("have.text", "Next tests about")
  })

  it("serves a 404 for unknown paths", () => {
    cy.request({
      url: `${app}/does-not-exist`,
      failOnStatusCode: false,
    }).then((response) => {
      expect(response.status).to.eq(404)
    })
  })
})
