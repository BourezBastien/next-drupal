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

## Resolved on the fork (status as of session 20, ordered sweep)

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
#485 (revisions/draft preview: resourceVersion is preserved end-to-end in 2.x — PreviewHandler stores it in previewData, getResourceFromContext falls back to it before rel:latest-version, draft.ts carries it; verified in preview.ts, next-drupal-pages.ts and draft.ts),
#415 (require_login guide added: module setup plus the exact path exclusions the frontend and next module need — /jsonapi*, /router/translate-path, /next/preview-url, /oauth/*, /subrequests/*),
#436 (Vercel Deployment Protection blocks the preview iframe with X-Frame-Options: deny — options documented in known-issues.mdx),
#521 (site resolver is already independent of draft mode in 2.x — the form select sits at the top level with no #states; its description only mentioned draft validation, now clarified to cover on-demand revalidation too),
#297 (revalidating other routes only: uncheck Revalidate page and fill Additional paths — existing configuration, documented in revalidator.mdx),
#63 (alias pattern changes: entity updates from the pattern regeneration trigger path revalidation in 2.x, and old paths are covered by the redirect-source flow of #911/#329 — the reporter confirmed the sync works after cache rebuild),
#286 (default locale prefix: workaround locked in with a test — omitting defaultLocale keeps the locale prefix for prefixed-default Drupal setups; an explicit configuration option remains a deferred enhancement, direction acknowledged upstream),
#135 (React Native: the 2.x core client has no runtime dependency on Next.js — only draft.ts and use-menu import next/*, and they are separate entry points; FAQ entry added),
#262 (yarn commands: the quick-start and umami guide already show npm and yarn alternatives),
#263 (Docker: FAQ entry added — separate PHP and Node containers, NEXT_PUBLIC_DRUPAL_BASE_URL wiring, ddev quick-start pointer),
#508 (unpublished node preview fatal: getPreviewUrlForEntity is now nullable (?Url) and the Iframe site previewer renders a translatable notice instead of a TypeError — kernel-tested),
#463 (site preview as an entity operation: next_entity_operation adds a Site preview link to listings for configured entity types — kernel-tested),
#158 (frontPage now accepts a per-locale record ({ default, de, en, ... }) with fallback to the default key then /home — jest-tested),
#162 (media by path requires the /media/{id} access setting — callout added to getResourceByPath docs),
#277 (Umami guide: Node 18 LTS+ requirement added — non-LTS Node releases fail the next-auth engine check),
#354 (patches still required: contrib bugs unfixed upstream; docs reference the current 2024 re-rolled decoupled_router patch),
#433 (translatePath now resolves paths per language: the Decoupled Router endpoint is prefixed with the locale via addLocalePrefix, and translatePathFromContext forwards the context locale — jest-tested),
#466 (getResourceFromContext no longer mutates the caller's context.locale — the entity langcode drives the path lookup through a copy; jest-tested with a de-de/de negotiation mismatch),
#465 (SEO guide added: Metatag via JSON:API, sitemap wiring through robots.ts, GTM with next/script, redirects pointer),
#447 (demo credentials documented in the FAQ: demo.next-drupal.org with example/example),
#448 (JSON:API index fetch failure: troubleshooting checklist in the FAQ — server-side reachability, subdirectory base URL, local https),
#452 (link field aliases: jsonapi_extras URL enhancer or the core computed-URL patch — FAQ entry),
#474 (install profile: jsonapi_hypermedia added to drupal/config/core.extension.yml, as proposed by upstream PR #491 — jsonapi_menu_items requires it),
#483 (getView now logs the endpoint in debug mode, matching the reporter's ask),
#489 (docs already say "UUID (client ID)" when describing the consumer — no client_uuid wording remains),
#491 (install profile: duplicate of #474, fixed by the jsonapi_hypermedia addition),
#493 (preview secret expired: duplicate of #422, covered by the known-issues entry),
#495 (edit forms no longer hijacked by the preview: the HtmlRenderer only takes canonical and revision routes, with the early return for empty sites from upstream PR #481 — verified in code),
#513 (multi-domain previewing: deferred — one preview_url per NextSite by design; per-domain or per-locale preview URLs need a design pass),
#516 (entity tokens in additional paths: the Path revalidator now replaces tokens like [node:url:path] via the token service (injected into RevalidatorBase, optional for BC), and the form description documents it — kernel-tested with [node:nid]),
#525 (422 preview debugging: the preview handler propagates Drupal errors with DRUPAL_DEBUG logging since the #818 fix; the "Method Not Allowed on /next/preview-url" from the issue is a GET-vs-POST misconfiguration),
#519 (basic pages: configure the node.page entity type at /admin/config/services/next/entity-types — question answered by configuration, reporter never followed up),
#523 (umami clone 404: no reply; covered by the FAQ checklist (#448) and the alias guidance (#469) — needs-repro),
#695 (sequential revalidations suspended per site: once a site's request fails, further paths skip it instead of stacking timeouts, with an explicit warning — kernel-tested),
#619 (getAccessToken now supports the password grant: pass { username, password } with client credentials configured on the client; user tokens are never cached — jest-tested),
#652 (Generate secret buttons on the next_site form fill the preview and revalidate secret fields with Crypt::randomBytesBase64(32), skipping validation and rebuilding — kernel-tested),
#595 (roadmap question: answered by upstream issue #692 — closed as duplicate).

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
Promise.all),
#96 (paragraph preview patches from 2022 are no longer referenced anywhere in
the docs and the reporter previewed fine without them; revision preview
works through the resourceVersion flow verified for #485),
#323 (client-side proxy API made obsolete by React Server Components —
component-level data fetching is the App Router answer; upstream closed on
this rationale),
#434 (auto-logout on Drupal user block: architecture question — validate the
access token server-side (simple_oauth introspection or a middleware check)
rather than trusting the session cookie; no library change needed),
#427 (hydration mismatch from dates: formatting dates inside client
components is the mésusage — format on the server or render the same string
on both sides; the umami example formats in server components only),
#256 (static paths in Drupal: deferred product enhancement — the workaround
pattern is a proxy node resolving to the same URL, as used by the maintainer;
needs a design decision),
#445 (queue for EntityEvents: deferred — the dispatcher already runs on
shutdown and its queue is drained after dispatch; a real queue worker is an
architectural project tied to multi-site benchmarks),
#132 (Drupal core preview mode via jsonapi_node_preview: deferred integration
project, tied to #217 and third-party preview APIs like GraphQL Compose),
#217 (tabbed/dual preview: deferred — the upstream design direction is a
placement block with a site selector in hybrid mode, not implemented),
#147 (404 paths for untranslated nodes with multiple locales: deferred
enhancement — filtering on langcode would change fallback behavior for sites
relying on untranslated-path serving; workaround is separate getPathsFromContext
calls per locale, as suggested upstream),
#97 + #99 + #744 (module dependencies — making simple_oauth, subrequests,
decoupled_router or pathauto optional: structuring decision, deferred. The
preview plugins depend on simple_oauth, the draft url validation on
decoupled_router; Drupal recipes (>= 10.3) were suggested upstream as the
delivery mechanism. Needs its own design pass),
#234 (usage catalog: community action upstream — maintainer planned a
showcase; nothing to do in the fork),
#321 (hacktoberfest topic: GitHub repository topic, maintainer action
upstream; not applicable to the fork),
#64 (site filtering from Drupal at the JSON:API level: deferred — identifying
the calling site requires a trusted signal (header/secret per site) plus
query alteration; the filter-by-site guide covers the frontend side),
#279 (same alias across microsites: by-design question — aliases are global
in Drupal; the pattern is per-site alias prefixes plus a rewrite, no library
change),
#461 (Workspaces integration: deferred — #876 added the workspace id to
NextSite, but previewing the active editor workspace requires forwarding the
workspace through the preview URL; design needed),
#507 (skip revalidation: per-type skip already exists (select None);
a global CLI/batch skip switch is a deferred enhancement),
#338 (search demo down: the example-search-api demo is hosted upstream and
out of service — nothing actionable in the repo; the search-api guide covers
the setup),
#349 (redirects empty: reporter resolved it themselves — the issue was in
their decoupled_router setup, not in this package),
#397 (syncDrupalPreviewRoutes bundle size: legacy helper — the upstream
recommendation is to inline the 5-line function in _app.tsx, which is also
what the maintainer does),
#406 (resource type not found: reporter traced it to PHP errors on their
/jsonapi install — environment issue, resolved by the reporter),
#421 (leaked metadata LogicException: triggered by the Rules module
rendering early during jsonapi_menu_items requests — third-party bug, not
actionable here),
#431 (Guzzle timeout during revalidation: deferred enhancement — a
configurable timeout per revalidator needs form + schema design; failures
are already logged since #696 and the Drupal http_client default applies),
#472 (sporadic 404s: no reproduction and no reply since 2023 — symptomatic
of ISR first-hit generation; fallback blocking covers it, needs-repro),
#479 (exposed view filters: tracked in the jsonapi_views contrib queue
(d.o. 3292906) — getView already forwards params, nothing to do here),
#482 (GraphQL taxonomy 500: drupal/graphql + graphql_compose beta
integration bug — third-party, resolved upstream since),
#488 (500 with __next_preview_data cookie: a Next.js bug fixed in later
versions, resolved by the reporter's upgrade).

## Process (from session 19, Bastien's directive)

Process open issues in ASCENDING numeric order (#1 → latest) instead of
thematic waves: it surfaces related problems early and keeps the sweep
auditable. Every issue gets a decision: fixed (with gates), documented,
classified, or explicitly deferred with a rationale. Resume from the lowest
open unprocessed number — see the ordered list in docs/PLAN.md.
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
