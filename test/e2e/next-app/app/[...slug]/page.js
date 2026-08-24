import { notFound } from "next/navigation"
import { NextDrupal } from "next-drupal"

const drupal = new NextDrupal(process.env.DRUPAL_BASE_URL)

export default async function CatchAllPage({ params }) {
  const path = params.slug ? `/${params.slug.join("/")}` : "/"

  const translatedPath = await drupal.translatePath(path)
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
