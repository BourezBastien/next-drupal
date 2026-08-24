/// <reference types="cypress" />

/**
 * Revision and menu smoke tests against the seeded Drupal site
 * (next_tests_seed): the home page carries two revisions and a menu link
 * points at it.
 */
context("Seed features", () => {
  const homeUuid = () =>
    cy
      .request(
        "/jsonapi/node/next_test_page?filter%5Btitle%5D=Next%20tests%20home&fields%5Bnode--next_test_page%5D=drupal_internal__vid"
      )
      .then((response) => {
        expect(response.body.data).to.have.length(1)
        return response.body.data[0].id
      })

  it("serves the first revision via resourceVersion=id:1", () => {
    homeUuid().then((uuid) => {
      cy.request(
        `/jsonapi/node/next_test_page/${uuid}?resourceVersion=id%3A1&fields%5Bnode--next_test_page%5D=drupal_internal__vid`
      ).then((response) => {
        expect(response.status).to.eq(200)
        expect(response.body.data.attributes.drupal_internal__vid).to.eq(1)
      })
    })
  })

  it("serves a newer latest revision", () => {
    homeUuid().then((uuid) => {
      cy.request(
        `/jsonapi/node/next_test_page/${uuid}?fields%5Bnode--next_test_page%5D=drupal_internal__vid`
      ).then((response) => {
        expect(response.status).to.eq(200)
        expect(
          response.body.data.attributes.drupal_internal__vid
        ).to.be.greaterThan(1)
      })
    })
  })

  it("exposes the seeded menu link", () => {
    cy.request("/jsonapi/menu_items/main").then((response) => {
      expect(response.status).to.eq(200)
      const titles = response.body.data.map((item) => item.attributes.title)
      expect(titles).to.include("Next tests home link")
    })
  })
})
