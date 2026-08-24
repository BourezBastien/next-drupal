import siteConfig from "site.config"
import type { DrupalNode, DrupalTaxonomyTerm } from "next-drupal"

export function truncate(value: string, length: number, suffix = "...") {
  if (value.length < length) {
    return value
  }

  return value.slice(0, length) + suffix
}

// The path attribute is typed as always present, but it is null at runtime
// for entities without a path alias yet (e.g. pathauto not configured).
// Fall back to the internal path so links never break.
export function nodeHref(node: DrupalNode): string {
  return node.path?.alias ?? `/node/${node.drupal_internal__nid}`
}

export function termHref(term: DrupalTaxonomyTerm): string {
  return term.path?.alias ?? `/taxonomy/term/${term.drupal_internal__tid}`
}

export function absoluteURL(uri: string) {
  return `${siteConfig.drupalBaseUrl}${uri}`
}

export function formatDate(input: string): string {
  const date = new Date(input)
  return date.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
  })
}

export function isRelative(url: string) {
  return !new RegExp("^(?:[a-z]+:)?//", "i").test(url)
}
