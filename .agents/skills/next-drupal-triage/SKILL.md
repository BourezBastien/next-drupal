---
name: next-drupal-triage
description: How to triage next-drupal GitHub issues and PRs — live-state commands, branch-protection constraints, ranked merge candidates and known stale PRs (audited 2026-08). Read before analyzing or recommending merges.
---

# next-drupal issue & PR triage

## Getting live state (the local `issues.json` / `prs.json` exports are UTF-16 and undated — prefer gh)

```bash
gh pr list -R chapter-three/next-drupal --state open \
  --json number,title,author,mergeable,mergeStateStatus,reviewDecision,updatedAt --limit 60
gh issue list -R chapter-three/next-drupal --state open --label bug --limit 50
gh pr diff <N> -R chapter-three/next-drupal
```

## Hard constraints

- Branch protection: every PR is `REVIEW_REQUIRED`; only maintainers can merge.
  Non-maintainers have READ on upstream — you can review/comment, not merge.
- ~14 open PRs are CONFLICTING/DIRTY (need rebase) — always check `mergeable`.
- 60+ open issues sit in `triage` label and ~55 have no label at all — labeling
  alone is valuable contribution.

## Merge candidates ranked (audit of 2026-08-24 — re-verify before acting)

**Safe, small, fix real bugs — recommend merging:**

1. **#865** (+1/−1, next-drupal pkg) — treat 403 like 404 in `translatePath` so
   archived content doesn't 500 (fixes #864). Matches existing 404 handling intent.
2. **#790 + #791** (+2/−1, example-umami) — `getParams` typing (`string | null`)
   and missing `return params` fallback. Trivially correct, complementary.
3. **#842** (+2/−2, www docs) — `getResource` docs use `'type'` not `'path'` (fixes #841).
4. **#904** (docs, by maintainer robdecker) — branch model + supported versions docs.

**Valuable but needs verification / maintainer decision:**

- **#853** (draft.ts +7/−2) — early-return in `getDraftData` before `cookies()` to
  unbreak SSG (fixes #852). Direction correct; verify Next.js static-generation
  behavior with `draftMode()` still called unconditionally.
- **#844** (next module +14/−17) — revalidate path alias instead of `node/#`
  (fixes #843). Right idea, refactor of `EntityActionEvent` — needs tests + careful
  review of revalidator side effects.
- **#876** (next module +9/−0) — workspace id in preview (issue #875). Additive
  feature, scope decision for maintainers.
- **#856** (next-drupal pkg +5/−0) — sends `host` to Decoupled Router. Real need
  (multi-site), but imports `next/headers` inside the client → breaks non-request
  contexts; should be opt-in via option. Suggest changes rather than merge as-is.

**Questionable / needs maintainer context:**

- **#860** (0/−1) — deletes the `unset(live_link)` line; may revert intentional
  unpublished-node behavior.

**Stale (CONFLICTING since 2024 or older) — candidates for close-or-rebase nudges:**
#795, #785, #775, #762, #758, #715, #703, #623, #584, #499, #398; plus ancient
mergeable ones likely obsolete: #67, #306, #425, #446, #491.

## Known linked issue→PR pairs (fix both sides of the story when triaging)

#875→#876, #867→#868, #864→#865, #852→#853, #845→#846, #843→#844, #841→#842.

## Recurring issue themes (where fixes have the most impact)

1. Preview / draft mode (v2 regression reports: #847, #859, #852)
2. Revalidation & cache (aliases, redirects, referenced entities: #843, #862, #911, #886)
3. i18n/locale paths (#854 — fixed on branch `fix/854-add-locale-prefix`)
4. Next.js 16 compatibility (#884)

## Classified as spec/by-design

- **#649** (filters not applied to individual resources): JSON:API spec — filters do not
  apply to individual resource reads, only to collections and includes. The
  parameters are forwarded correctly by the client.
- **#682** (getMenu + output:export): needs a static-export reproduction; likely
  revalidate/cache semantics during prerender.

## Issues parked as needs-reproduction (do not force a blind fix)

- **#849** (jsona returns `{type,id,links}` skeletons): no reproduction in our
  harness; reporter's raw JSON:API responses look fine. Next step: ask for the
  raw `/jsonapi/node/x` payload and the next-drupal + Drupal versions; suspect
  a contrib module altering the payload (JSON:API extras) or a proxy.
- **#857** (headless view missing on canonical tab): `HtmlRenderer::prepare`
  already covers `entity.$type.canonical` (verified in source), so the report
  contradicts the code path — needs an interactive Drupal reproduction. Check
  the site resolver actually resolving sites for the canonical entity, and the
  `site_previewer` setting.

## Classified as by-design (do not force a fix)

- **#776** (multiple OAuth tokens): each server process generates and caches its own
  token (build workers + runtime instances). Expected behavior; a shared token
  cache would be an opt-in feature, not a bug fix.

## Resolved on the fork (status as of session 14)

#854, #499, #874, #912, #861, #855, #859, #847, #911, #862, #848, #850, #686, #799, #681, #772, #722, #779, #155, #346, #650, #793, #813, #818, #246, #533, #783 (docs ddev/consommateur), #806 (numérotation umami + patches déjà corrigés), #611 (pattern next-auth preview), #653 (base_url rendered as a link in the next_site listing, kernel-tested), #696 (Path revalidator logs non-200 responses as warnings, kernel-tested), #615 (consumer collection path resolved via Url::fromRoute with a safe fallback, kernel-tested), #532 + #535 (version added to modules/next/package.json — no more yarn workspace warning; the workspace package is renamed next-drupal-module because the original "next" name shadowed the Next.js framework once versioned), #422 + #493 (preview secret expiration documented in known-issues.mdx: refresh the preview or raise secret_expiration),
#325 (translatePath now sends Accept: application/json — the decoupled_router route requires the json format and older Drupal versions fail to negotiate application/vnd.api+json to it; jest-tested),
#326 (example-umami JWT cache keyed by the Drupal user id decoded from the token claims, so rotated tokens land in the same entry and sessions no longer share entries by rotating access tokens),
#419 (Drupal cache recommendations documented in cache.mdx),
#437 (authenticated user details via hook_simple_oauth_private_claims_alter documented in password-grant.mdx),
#467 (to-many relationship bodies — the JsonApiResourceBodyRelationship union has supported arrays since the #874 fix; locked in with a mocked createResource jest test),
#468 (path of the created resource available on the deserialized response — redirect example documented in creating-resources.mdx),
#456 (paginated collections — the withMeta option returns {results, meta, links}; example documented in fetching-resources.mdx),
#469 (umami example links now fall back to the internal path via nodeHref/termHref when path.alias is null — the root cause is entities without path aliases, e.g. pathauto not configured),
#438 (page-limit guide: exact services.yml location, copy step from default.services.yml, drush cr, and a valid YAML snippet — the old `// comment` broke the file when copied),
#592 (esm/cjs builds smoke-tested: scripts/verify-builds.mjs loads dist/index.js and dist/index.cjs and asserts the public exports from both; wired into CI quality.yml and as `yarn test:builds`),
#329 (alias changes: with the redirect module the old path is revalidated on the redirect source — covered by the #911 fix and its kernel test; documented in revalidator.mdx that the module is required, Drupal keeps no trace of the old alias otherwise),
#370 (trailingSlash revalidation: normalization snippet documented in revalidator.mdx — the upstream-endorsed workaround),
#179 (republish flow: the module sends the update event and the starter uses notFound() (#772); the remaining stale-404 behavior was a Next.js bug fixed in 13.4 — documented in known-issues.mdx),
#453 (DRUPAL_PREVIEW_SECRET deprecated in 2.0 and fully removed from docs, starters, examples and e2e — the secret lives on the Drupal side and is signed into the preview URL),
#485 (revisions/draft preview: resourceVersion is preserved end-to-end in 2.x — PreviewHandler stores it in previewData, getResourceFromContext falls back to it before rel:latest-version, draft.ts carries it; verified in preview.ts, next-drupal-pages.ts and draft.ts).

Already resolved by adopted/other work (no further action): #148 (preview alerts already use a plain <a>, fix from the issue thread applied upstream), #740 (flaky coverage — Node bug mitigated by the .nvmrc v18.19 pin, referenced in .nvmrc), #838 (v1.6 menu link already correct), #581 (ESM + sideEffects:false already shipped), #746 (fixed by adopted PR #747),
#589 (permission list incl. View all revisions already in the draft-mode guide),
#93 (getStaticPathsFromContext already parallelizes types AND locales with
Promise.all — verified in next-drupal-pages.ts),
#579 (client-credentials token fetch on write operations is the designed auth
flow; tokens are cached and reused — getAccessToken checks expiry and same
clientId/clientSecret/scope; the reporter's auth server simply lacks the
client_credentials grant),
#158 (front page per locale needs a locale→frontPage map — API design decision,
documented workaround in the issue; deferred),
#613 (Turbopack: nothing bundler-specific in the client — plain fetch + qs;
support follows the Next.js version, no action possible in the library),
#530 (.nvmrc already pinned to v18.19 with the #740 justification; engines
keeps >=16 for the 2.x line per the upstream decision to drop v16 in 3.x),
#515 (getMenu sparse fieldsets: the jsonapi_menu_items endpoint is a custom
contrib endpoint that ignores fields[...] params — reduce fields via JSON:API
resource overrides at /admin/config/services/jsonapi/resource_types, per the
issue's own resolution),
#476 (port in NEXT_PUBLIC_DRUPAL_BASE_URL → 404 on subpages: multi-site setups
match decoupled_router resolution by host; a port suffix changes the host
string. Needs a reproduction to act on; workaround is omitting the default
port),
#271 (two preview routes: canonical pages get the Next iframe via the site
previewer, while Drupal's node/preview/{id}/full route renders the core
preview unless the previewer is attached there — by-design behavior of the
site_previewer configuration, no bug),
#240 (async getResource in forEach: JavaScript mésusage — forEach does not
await promises; the reporter solved it themselves with a for loop or
Promise.all).
Adopted upstream PRs: #865, #790, #791, #842, #904, #853, #844, #876, #856
(adapted as `host` option), #860 (#846 was already fixed upstream by #887).

## E2E Cypress blocker — how to lift it

Execution requires content that only exists in Chapter Three's private Drupal
database (faker-generated strings asserted verbatim by the specs, see
TESTING.md). Two ways forward, pick one when prioritizing E2E:

1. **Obtain a DB copy** — ask Chapter Three (Drupal Slack `#nextjs` channel)
   for the tests.next-drupal.org database dump + files, restore it in `drupal/`.
2. **Deterministic install profile** — build a `next_tests` install profile
   (config + seeded demo content matching the specs), then rewrite the spec
   assertions against the seed. Bigger but self-contained; already a TODO in
   TESTING.md.

Until then, E2E is out of every gate; unit/Kernel gates remain authoritative.

## Etiquette

- Comment in English on GitHub regardless of local language.
- When reviewing a PR: check CI, `mergeable`, linked issue, test coverage of the
  changed behavior (100% coverage policy applies to new `src/` code).
- Don't push to upstream; branches go to the contributor fork (`origin`).
