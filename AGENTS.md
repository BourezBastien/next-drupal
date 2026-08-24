# AGENTS.md

Monorepo for **next-drupal** — Next.js helpers for Drupal (JSON:API), the `next` Drupal module, starters, examples, and the docs site. Managed with Yarn 1 workspaces + Turborepo (build) + Lerna (versioning/publishing).

## Mandatory project skills (read before acting)

This repo carries its own skills in `.agents/skills/`. **Always read the relevant one before starting work:**

- `.agents/skills/next-drupal-dev/SKILL.md` — before writing/building/testing any code (how to run tests without a live Drupal site).
- `.agents/skills/next-drupal-conventions/SKILL.md` — before editing `packages/next-drupal` or `modules/next` (class hierarchy, TypeScript status, patterns, formatting, commit scopes).
- `.agents/skills/next-drupal-triage/SKILL.md` — before triaging issues/PRs or recommending merges (ranked merge candidates, stale PRs, constraints).

## Layout

- `packages/next-drupal/` — the npm package. TypeScript, built with tsup (ESM + CJS + dts). Entry points: `src/index.ts`, `src/draft.ts`, `src/navigation.ts`.
- `modules/next/` — Drupal module (PHP). Released via drupal.org, **not** Lerna.
- `drupal/` — full Drupal install used for Cypress e2e tests (requires `composer install` there first).
- `examples/*`, `starters/*` — apps that sync to their own separate git repos via `scripts/sync-repo.sh`.
- `www/` — next-drupal.org docs site.

## Commands

```bash
yarn                       # install (Yarn 1 workspaces)
yarn lint                  # eslint (js/ts/tsx across repo)
yarn format                # prettier --write .   (pretest runs format:check)
yarn test                  # jest for packages/next-drupal
yarn workspace next-drupal dev   # tsup --watch for the package
yarn test:next             # phpunit for modules/next (needs composer install in /drupal)
yarn phpcs                 # PHP CodeSniffer for modules/next
yarn test:e2e:ci           # Cypress e2e (needs a Drupal db/files not in this repo)
```

## Critical constraints

- **Jest tests need a live Drupal instance + secrets.** Copy `packages/next-drupal/.env.example` to `.env`; real credentials point at tests.next-drupal.org (CI-only for most contributors). Tests will fail without these env vars — this is expected locally, not a code bug.
- **100% coverage threshold** (statements/branches/functions/lines) is enforced in `packages/next-drupal/jest.config.cjs`. New code in `src/` requires tests; `src/deprecated*`, `src/navigation.ts`, and `src/types/*` are excluded.
- **Node version is pinned to v18.19** in `.nvmrc` — v18.20+/v20.10+ have a Jest coverage bug (#740).
- **Conventional Commits are enforced** (commitlint + husky). Scope = folder name: `next-drupal`, `next`, `basic-starter`, `example-auth`, etc. Lerna derives versions and CHANGELOG from these commits; a bad commit message has release consequences.
- **Never run `sync:*` scripts or `lerna publish/version` casually** — they push to external git repos (drupal.org, chapter-three/\*) and npm. Release procedure is documented in `MAINTAINING.md`.
- PHP and JS live in one repo: run `yarn phpcs`/`yarn test:next` for `modules/next`, `yarn lint`/`yarn test` for everything else.
- Several root scripts (`test:next`, `sync:*`) are bash-style; on Windows use Git Bash.

## Package architecture (`packages/next-drupal/src/`)

- `next-drupal-base.ts` → `NextDrupalBase` (shared client logic)
- `next-drupal-pages.ts` → Pages Router client
- `next-drupal.ts` → App Router client
- `draft.ts` → draft mode (experimental, separate export `next-drupal/draft`)
- The package is ESM-first (`"type": "module"`) with dual ESM/CJS exports maps including `.d.cts` types — preserve both formats when editing `package.json` exports or tsup config.

## Docs to read before sensitive changes

- `CONTRIBUTING.md` — commit conventions, testing overview.
- `MAINTAINING.md` — release/sync procedures (read before any release or sync work).
- `TESTING.md` — test setup details.
