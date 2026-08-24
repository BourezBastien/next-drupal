// Smoke test for the published package builds: load the ESM and CJS
// bundles and verify the public API is exported from both.
// Run after building the package (yarn workspace next-drupal prepare).
import { createRequire } from "node:module"
import path from "node:path"
import { fileURLToPath, pathToFileURL } from "node:url"

const require = createRequire(import.meta.url)

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "..")

const expectedExports = [
  "NextDrupal",
  "NextDrupalPages",
  "DrupalClient",
  "NextDrupalBase",
  "JsonApiErrors",
  "PreviewHandler",
  "buildUrl",
  "deserialize",
]

const cjs = require(path.join(root, "packages/next-drupal/dist/index.cjs"))
for (const name of expectedExports) {
  if (!(name in cjs)) {
    throw new Error(`CJS build is missing export: ${name}`)
  }
}

const esm = await import(
  pathToFileURL(path.join(root, "packages/next-drupal/dist/index.js")).href
)
for (const name of expectedExports) {
  if (!(name in esm)) {
    throw new Error(`ESM build is missing export: ${name}`)
  }
}

console.log(
  `Builds verified: ESM and CJS expose the ${expectedExports.length} expected exports.`
)
