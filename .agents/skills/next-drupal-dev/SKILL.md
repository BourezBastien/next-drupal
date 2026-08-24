---
name: next-drupal-dev
description: Development workflow for the next-drupal monorepo — setup, build, lint, and how to run targeted tests without a live Drupal site. Read before writing or testing any code in this repository.
---

# next-drupal development workflow

## Environment

- Yarn 1 workspaces (`packageManager: yarn@1.22.15`). If `yarn` is missing, use `npx -y yarn@1.22.15 <cmd>`.
- Node is pinned to **v18.19** in `.nvmrc` (v18.20+/v20.10+ have a Jest coverage bug, issue #740). Newer Node works for unit tests if you disable coverage.
- Install once at the root: `yarn install` (also runs `prepare` → tsup build + husky).

## Commands (run from repo root)

```bash
yarn lint                  # ESLint over the whole repo
yarn format                # Prettier (CI runs format:check — always format before committing)
yarn format:check
yarn test                  # full Jest suite for packages/next-drupal (needs live Drupal)
yarn phpcs                 # PHP_CodeSniffer for modules/next
yarn test:next             # PHPUnit for modules/next (needs composer install in /drupal first)
```

## Running tests WITHOUT a live Drupal site

Most tests that hit the network require `DRUPAL_BASE_URL` + OAuth secrets against
tests.next-drupal.org and cannot run locally without credentials. **Pure unit
tests can run anywhere** — target the specific file and disable coverage:

```bash
cd packages/next-drupal
DRUPAL_BASE_URL=http://localhost npx jest tests/NextDrupalBase/basic-methods.test.ts --coverage=false
```

Good unit-test-only suites: `tests/NextDrupalBase/*` (constructor, basic
methods, getters/setters), `tests/Logger`, `tests/DrupalMenuTree`, `tests/utils`.

## Coverage rule

`jest.config.cjs` enforces **100% coverage** (statements/branches/functions/lines)
on `src/` (excluding `deprecated*`, `navigation.ts`, `types/*`). Any code you add
to `src/` must come with tests, or CI fails. Never weaken the thresholds.

## Build

`packages/next-drupal` builds with tsup from three entries (`index.ts`,
`draft.ts`, `navigation.ts`) into dual ESM/CJS + `.d.ts`/`.d.cts`. Run
`yarn workspace next-drupal prepare` or watch with
`yarn workspace next-drupal dev`.

## Branch and commit rules

- Never commit directly to `main`. One branch per fix, named e.g. `fix/854-add-locale-prefix`.
- Conventional Commits are **enforced** by commitlint + husky. Scope = folder
  name: `fix(next-drupal): ...`, `feat(next): ...` (Drupal module),
  `fix(basic-starter): ...`, `docs: ...`. Footer `Fixes #NNN` to close issues.
- Prettier: **no semicolons**, double quotes in JS/TS (PHP files in `modules/`
  use semicolons + single quotes — see `.prettierrc.json` overrides).
- `lint-staged` runs on commit; a failed hook means lint/format issues to fix.

## Git remotes

`origin` = contributor fork (push here), `upstream` = chapter-three/next-drupal
(READ-only for non-maintainers; branch protection requires a review before merge).
