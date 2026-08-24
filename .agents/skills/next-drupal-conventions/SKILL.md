---
name: next-drupal-conventions
description: Code conventions and architecture rules of the next-drupal codebase (OOP class hierarchy, TypeScript status, JSDoc, formatting, commits) — verified by audit, with the gaps to not make worse. Read before editing packages/next-drupal or modules/next.
---

# next-drupal code conventions (audited 2026-08)

## Architecture — client class hierarchy

```
NextDrupalBase                 (next-drupal-base.ts)  — auth, buildUrl, locale, CRUD primitives
  └── NextDrupal               (next-drupal.ts)       — App Router client: translatePath, subrequests, menus, draft
        └── NextDrupalPages    (next-drupal-pages.ts) — Pages Router client: context-aware methods, getStaticProps era API

JsonApiErrors extends Error    (jsonapi-errors.ts)    — typed JSON:API error container
DrupalMenuTree<T>              (menu-tree.ts)         — generic tree structure for menus
```

Rules when editing:

- Put shared logic as high as possible in the chain; a method only on the class
  whose router it targets. `NextDrupalPages` extends `NextDrupal` (not the base)
  — don't break that order.
- Public API surface is exported via `src/index.ts` (and `src/draft.ts` for the
  experimental `next-drupal/draft` subpath). New public methods must be re-exported
  there and documented.
- Methods returning resources are generic: `getResource<T extends JsonApiResource>(...)`.
  Keep the generic pattern; do not return `any`.

## Current state (verified) and the rules that follow

| Aspect | State | Rule for agents |
|---|---|---|
| TypeScript strict mode | **off** (`strict: false` in tsconfig) | Do not assume strict null checks; still write types as if strict — explicit param/return types on public methods |
| `any` usage | ~none outside `types/resource.ts` + `deprecated/` (which carry eslint-disable) | Never add new `any`; extend types in `src/types/` instead |
| Access modifiers | Almost none (everything implicitly public by convention) | Match existing style — do not add `private`/`protected` piecemeal; it's a deliberate repo-wide convention, changing it is a separate refactor |
| JSDoc | Systematic on public methods (`@param`, `@returns`, `@example`) | Required for new/changed public methods |
| Inheritance vs composition | Inheritance-based by design | Don't introduce composition refactors ad hoc |
| Design patterns | Options-object parameters (`options: {...} = {}`), class-based client with instance config, Error subclassing | Follow the same patterns; no singleton/service-locator patterns in this package |

## Formatting (prettier, enforced by lint-staged + CI)

- JS/TS: no semicolons, double quotes, trailing commas (es5), 80 cols.
- `modules/**` (PHP): semicolons, single quotes, trailing commas all.
- Run `yarn format` before committing; `yarn format:check` runs in pretest.

## Commits (commitlint, conventional)

`<type>(<scope>): <subject>` — lowercase; scope = workspace folder name:
`next-drupal` (packages/next-drupal), `next` (modules/next), `basic-starter`,
`graphql-starter`, `pages-starter`, `example-*`, `www`, or empty for global.
Types: feat, fix, docs, style, refactor, perf, test, build, ci, chore, revert.
Breaking changes: `!` + `BREAKING CHANGE:` footer (drives Lerna versioning).
Reference issues in the footer: `Fixes #NNN`.

## PHP module (modules/next)

Drupal coding standard, checked by `yarn phpcs` (phpcs.xml). PHPUnit tests under
`modules/next/tests`. Drupal `.module`/`.install` hooks and plugin system
(Annotation-based plugins in `src/Plugin/Next/...`). Releases go through
drupal.org git, not Lerna — never bump module version numbers in package.json.
