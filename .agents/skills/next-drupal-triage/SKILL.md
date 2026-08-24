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

## Etiquette

- Comment in English on GitHub regardless of local language.
- When reviewing a PR: check CI, `mergeable`, linked issue, test coverage of the
  changed behavior (100% coverage policy applies to new `src/` code).
- Don't push to upstream; branches go to the contributor fork (`origin`).
