import { notFound } from "next/navigation"
import { NextDrupal } from "next-drupal"

// The home renders live content from Drupal: prerendering it at build time
// would freeze the front page and break when Drupal is unavailable.
export const dynamic = "force-dynamic"

// The front page path is configurable, mirroring real sites: with
// DRUPAL_FRONT_PAGE=/next-tests/home the home renders the seeded page.
export default async function Home() {
  const drupal = new NextDrupal(process.env.DRUPAL_BASE_URL, {
    frontPage: process.env.DRUPAL_FRONT_PAGE ?? "/home",
  })

  const translatedPath = await drupal.translatePath(
    drupal.constructPathFromSegment([])
  )
  if (!translatedPath) {
    notFound()
  }

  const node = await drupal.getResource(
    translatedPath.jsonapi.resourceName,
    translatedPath.entity.uuid
  )
  if (!node) {
    notFound()
  }

  return (
    <main>
      <h1 data-cy="node-title">{node.title}</h1>
      <p data-cy="node-type">{node.type}</p>
    </main>
  )
}
