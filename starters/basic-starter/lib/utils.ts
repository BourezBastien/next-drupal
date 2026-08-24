export function formatDate(input: string): string {
  const date = new Date(input)
  return date.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
  })
}

export function absoluteUrl(input: string) {
  const baseUrl = process.env.NEXT_PUBLIC_DRUPAL_BASE_URL as string

  // Already absolute: return as-is.
  if (/^https?:\/\//i.test(input)) {
    return input
  }

  // Drupal in a subdirectory can return paths that already include the
  // base path: avoid duplicating it. (#729)
  const url = new URL(baseUrl)
  const basePath = url.pathname.replace(/\/+$/, "")
  if (basePath && input.startsWith(basePath)) {
    return `${url.origin}${input}`
  }

  return `${baseUrl.replace(/\/+$/, "")}${input}`
}
