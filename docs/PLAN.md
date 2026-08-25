# Plan stratégique — next-drupal (fork Bastien)

> Document de référence de la session. Toute IA intervenant sur ce dépôt doit le lire.
> Règle d'or : **tout se fait sur NOTRE fork (`origin` = BourezBastien/next-drupal). Aucune PR, aucun commentaire, aucun push vers `chapter-three/next-drupal` (upstream) sans instruction explicite de Bastien.**

---

## État d'avancement (mis à jour 2026-08-24, session 20)

### Mode opératoire (directive Bastien, session 19)
Parcours des issues ouvertes **par ordre croissant de numéro** (#1 → dernière) au lieu de vagues thématiques : chaque issue reçoit une décision (fix + gates, doc, classification, ou deferral justifié). Reprise au plus petit numéro ouvert non traité.

### Issues GitHub résolues ou classées avec justification (163 — PARCOURS ORDONNÉ TERMINÉ : les 169 issues ouvertes sont toutes traitées ou documentées)
#854 (locale prefix), #499 (type promote), #874 (meta relationships), #912 (sous-répertoire), #861 (locale + cache tags), #855 (debug sites vides), #859 (lien live révisions), #847 (docs preview→draft), #911 (revalider la source des redirects, avec test kernel), #862 + #848 (docs du revalidator cache_tag), #850 (pagination des chemins statiques, avec tests), #686 (types `drupal_internal__*id` en number), #799 (`credentials` seulement si le runtime le supporte, avec test), #681 (contrainte du générique de `getMenu` — réglée par le retypage `T extends DrupalMenuItem`), #772 (starter : notFound() au lieu de throw → la revalidation à la dépublication réussit), #722 (option withMeta sur getResourceCollection → {results, meta, links} avec type DrupalResourceCollection), #779 (l'événement d'entité porte la langue → le chemin de la traduction supprimée est revalidé, test kernel), #155 (injection automatique de default_langcode dans les sparse fieldsets from-context, avec test), #346 (plugin de génération d'URL de preview effaçable → désinstallation possible, avec schema fallback et test), #650 (docs : flag extra.enable-patching requis par composer-patches 2.x), #793 (arbre de menu toujours retourné → sérialisable getStaticProps), #813 (docs : avertissement withAuth sur pages publiques + bypass access), #818 (handler preview v1 : erreurs Drupal propagées avec logging DRUPAL_DEBUG), #246 (invalidation du tag rendered à la sauvegarde/suppression des configs d'entités), #533 (TESTING.md : recettes locales unitaires documentées), #783 (docs quick-start : ddev + consommateur), #806 (guide umami : numérotation corrigée, auth déjà documentée en fin de guide), #611 (pattern next-auth pour preview authentifié). Déjà résolues sans action : #746, #589, #838 (lien v1.6 correct), #581 (ESM + sideEffects:false déjà en place), #148 (alertes preview déjà en <a> simple), #740 (couverture instable — bug Node, atténué par le pin .nvmrc). #649 (limite spec JSON:API, classée) et #682 (needs repro avec output:export, parkée). Session 8 : #653 (base_url cliquable dans la liste des sites, avec tests kernel), #696 (échecs de revalidation non-200 désormais logués en warning dans le revalidator Path, avec test), #93 (classée déjà-résolue : `getStaticPathsFromContext` parallélise déjà types ET locales via `Promise.all`). Session 9 : #615 (chemin de la collection consumers résolu via `Url::fromRoute` avec repli défensif, test kernel avec module consumers activé), #532 + #535 (version ajoutée à modules/next/package.json — plus de warning yarn), #422 + #493 (expiration du secret de preview documentée dans known-issues.mdx), classées : #579 (flux client_credentials par conception, token mis en cache et réutilisé), #158 (front page par locale = décision d'API, contournement documenté), #613 (rien de spécifique au bundler dans le client). Session 10 : #325 (translatePath envoie Accept: application/json — la route decoupled_router exige le format json et certaines versions de Drupal ne négocient pas vnd.api+json vers json ; test jest), #326 (cache JWT de l'exemple umami clé par uid décodé du token — plus de collision entre utilisateurs ni d'incohérence de clé après rotation), #419 (recommandations de cache Drupal documentées dans cache.mdx), #437 (détails utilisateur via hook_simple_oauth_private_claims_alter documentés dans password-grant.mdx). Session 11 : #467 (corps de relation to-many — l'union JsonApiResourceBodyRelationship le supporte depuis le fix #874, verrouillé par un test jest mocké), #468 (chemin de la ressource créée lisible sur la réponse désérialisée — exemple de redirect documenté), #456 (pagination — l'option withMeta retourne {results, meta, links}, exemple documenté), #530 classée (.nvmrc déjà sur v18.19 avec justification #740, engines v16 maintenu en 2.x par décision upstream). Session 12 : #469 (liens de l'exemple umami sécurisés par nodeHref/termHref avec repli sur le chemin interne quand l'alias est null — racine : entités sans alias pathauto), #438 (guide page-limit : emplacement exact de services.yml, copie depuis default.services.yml, drush cr, snippet YAML valide — l'ancien commentaire `//` cassait le fichier copié), #515 classée (endpoint contrib jsonapi_menu_items qui ignore fields[...] → resource overrides JSON:API), #476 classée needs-repro (multi-site : decoupled_router match par host, le port change la chaîne). Session 13 : #592 (builds ESM/CJS vérifiés : scripts/verify-builds.mjs charge dist/index.js et dist/index.cjs et asserte les exports publics des deux formats ; intégré à quality.yml et en `yarn test:builds`), #329 (changement d'alias : avec le module redirect l'ancien chemin est revalidé via la source du redirect — couvert par le fix #911 et son test kernel ; doc revalidator.mdx expliquant que le module redirect est requis), #370 (trailingSlash : snippet de normalisation documenté dans revalidator.mdx, contournement validé upstream). Session 14 : #179 (republish → 404 : module + starter corrects, résidu = bug cache 404 des vieux Next.js corrigé en 13.4 — entrée known-issues.mdx), #271 classée (deux routes de preview = comportement par design du site_previewer), #453 (DRUPAL_PREVIEW_SECRET déprécié en 2.0 et totalement absent du repo — vérifié docs/starters/examples/e2e), #485 (resourceVersion préservé de bout en bout en 2.x : previewData → getResourceFromContext avant rel:latest-version, vérifié dans preview.ts, next-drupal-pages.ts, draft.ts), #240 classée (mésusage forEach async, résolu par le reporter). Session 15 : #415 (guide require-login.mdx créé avec les exclusions de chemins exactes + navigation), #436 (X-Frame-Options Vercel Deployment Protection documenté dans known-issues.mdx), #96 classée (patches paragraphs 2022 plus référencés nulle part, preview de révisions couverte par resourceVersion), #323 classée (proxy client rendu obsolète par les Server Components), #434 classée (logout auto = validation serveur du token, question d'architecture), #427 classée (mismatch d'hydratation = formatage de dates côté client). Session 16 : #521 (site resolver déjà indépendant du draft mode en 2.x — select de premier niveau sans #states, vérifié dans le form ; description clarifiée pour couvrir la revalidation), #297 (revalider d'autres routes uniquement : décocher Revalidate page + Additional paths — config existante documentée dans revalidator.mdx), #256 différée (chemins statiques côté Drupal = enhancement produit, pattern du nœud proxy utilisé par le mainteneur), #445 différée (queue EntityEvents = refacto architectural, dispatcher déjà en shutdown + drain après dispatch). Session 17 : #63 (changement de pattern d'alias : les updates de régénération déclenchent la revalidation en 2.x + anciens chemins couverts par #911/#329, reporter a confirmé la sync), #286 (préfixe de la locale par défaut : contournement verrouillé par un test — omettre defaultLocale conserve le préfixe ; option explicite = enhancement différée), #132 différée (intégration jsonapi_node_preview, liée #217 et GraphQL Compose), #217 différée (design upstream : block + site selector en mode hybride, non implémenté), #147 différée (filtrage langcode changerait le comportement de fallback ; contournement = appels séparés par locale). Session 18 : #135 (React Native possible depuis la 2.x — aucun import runtime next/* hors entry points draft/navigation, entrée FAQ), #262 (commandes yarn déjà présentes dans quick-start et guide umami), #263 (entrée FAQ Docker : conteneurs séparés PHP/Node + NEXT_PUBLIC_DRUPAL_BASE_URL), #97+#99+#744 différées (dépendances optionnelles = décision structurante, piste des recipes ≥10.3), #234 classée (showcase communautaire upstream), #321 classée (topic GitHub = action mainteneur). Session 19 : #508 (fatal preview nœud non publié : getPreviewUrlForEntity désormais nullable ?Url + notice traduisible dans le site previewer Iframe au lieu d'un TypeError — tests kernel), #463 (opération « Site preview » ajoutée aux listings via hook_entity_operation pour les types configurés — test kernel), #64 différée (filtrage par site au niveau JSON:API : signal de confiance à concevoir), #279 classée (alias globaux par design, pattern préfixe par site), #461 différée (preview du workspace actif : design), #507 différée partiellement (skip par type = None existe ; skip global CLI/batch à concevoir). Session 20 (début du parcours ordonné) : #158 (frontPage accepte désormais un record par locale avec fallback « default » puis /home — tests jest ; réponse au besoin DRUPAL_FRONT_PAGE par langue), #162 (accès aux médias par chemin : callout sur le réglage /media/{id} dans la doc getResourceByPath), #277 (prérequis Node 18 LTS+ ajouté au guide umami), #338 classée (démo search hébergée upstream hors service), #349 classée (résolue par le reporter : problème decoupled_router), #354 classée (patches contrib toujours requis, docs à jour avec le patch 2024). Session 20 suite : #433 (translatePath résout désormais par langue : endpoint decoupled_router préfixé par la locale via addLocalePrefix + propagation du contexte dans translatePathFromContext — tests jest), #397 classée (helper legacy, recommandation upstream = inliner la fonction), #406 classée (résolue par le reporter : erreurs PHP sur son install), #421 classée (bug tiers module Rules), #431 différée (timeout Guzzle configurable à concevoir ; échecs déjà logués #696). Session 21 : #466 (getResourceFromContext ne mute plus le context.locale de l'appelant — le langcode de l'entité pilote la résolution de chemin via une copie du contexte ; test jest avec mismatch de-de/de), #465 (guide SEO créé : Metatag, sitemap, GTM, redirects), #447 (credentials de la démo documentés en FAQ), #448 (checklist de dépannage « Failed to fetch JSON:API index » en FAQ), #452 (alias des champs liens : enhancer jsonapi_extras — FAQ). Session 22 : #474 (profil d'install : jsonapi_hypermedia ajouté à drupal/config/core.extension.yml comme proposé par la PR upstream #491), #483 (getView logue l'endpoint en mode debug), #472 classée needs-repro (404 sporadiques sans reproduction, symptôme ISR), #479 classée (suit le module contrib jsonapi_views), #482 classée (bug d'intégration GraphQL contrib), #488 classée (bug Next.js corrigé, résolu par le reporter). Session 23 : #489 résolue (la doc dit déjà « UUID (client ID) », plus aucune mention client_uuid), #491 résolue (doublon de #474), #493 résolue (doublon de #422, entrée known-issues), #495 résolue (le HtmlRenderer ne prend que canonical + révisions avec early return sites vides du fix #481 — vérifié dans le code), #513 différée (preview multi-domaines = conception), #515 déjà classée (vague 12). Session 24 : #516 (tokens d'entité dans les chemins additionnels de revalidation : le Path revalidator remplace les tokens [node:url:path] via le service token injecté dans RevalidatorBase (paramètre optionnel pour compat), description du formulaire mise à jour — test kernel avec [node:nid]), #525 classée (erreurs 422 propagées + logging depuis #818 ; le Method Not Allowed = mésusage GET/POST), #519 classée (question de config sans suivi), #523 classée needs-repro (couverte par les FAQ #448/#469). Session 25 : #695 (revalidations suspendues par site : dès qu'une requête échoue vers un site, les chemins suivants le skipper au lieu d'empiler les timeouts, warning explicite — test kernel), #619 (getAccessToken supporte le password grant via { username, password } avec les client credentials du client ; les tokens utilisateur ne sont jamais mis en cache — tests jest), #652 (boutons « Generate secret » sur le formulaire next_site remplissent preview_secret/revalidate_secret avec Crypt::randomBytesBase64(32) et rebuildent — tests kernel), #595 classée (question roadmap renvoyée vers #692). Session 26 : #729 (duplication du sous-répertoire dans les chemins d'images : absoluteUrl du starter basic et absoluteURL de l'exemple umami ne dupliquent plus le base path quand Drupal le renvoie déjà, URLs absolues passées telles quelles, double slash normalisé — vérifié contre le scénario rapporté), #732 (build example-marketing : import domhandler racine corrigé + images.domins fallback tableau vide ; le reste relève du chantier modernisation), #699 (FAQ SSO : sessions séparées par conception), #713 classée (dependabot superseded par #714), #727 classée (draft mode opt-in par type d'entité). Constat : depuis la reconstruction du dist (vague 13), les examples consommant le package lié voient les types unknown de la vague 8 — example-umami passe de 3 à 51 erreurs tsc (préexistantes hors gates, aucune dans nos fichiers) ; à traiter dans le chantier modernisation des examples. Session 27 : #788 (token périmé : la requête de token envoie cache: "no-store" pour que la couche fetch (Next 14 cachait par défaut) ne serve jamais un token périmé — assertion jest ; Next 15 a corrigé le défaut), #753 classée (pagination de pages de termes : réponse upstream = rendu serveur/client, getStaticPaths inadapté, withMeta fournit les données), #754 classée (access control consommateur : question de grants/scopes Drupal), #769 classée (doc : largement adressée par les guides ajoutés sur le fork), #771 classée (données minimales : sparse fieldsets + includes déjà supportés et documentés).

### PR upstream adoptées (avec attribution Co-authored-by)
#865, #790, #791, #842, #904, #853 (durci + test SSG), #844 (durci garde null), #876, #856 (réimplémenté en option `host` explicite), #860. #846 déjà résolue en amont (#887). Dependabot : qs → 6.15.3 et nanoid → 3.3.18 appliqués nous-mêmes (remplace #908, #929).

### Bugs découverts et corrigés au passage
- `EntityActionEventDispatcher::destruct()` ne vidait pas sa file → revalidations HTTP dupliquées (processus longs).
- Schéma de config manquant pour le revalidator `cache_tag` (impossible de sauver la config en strict).
- Test instable `SimpleOauthPreviewUrlGeneratorTest` (timestamp `now` expirant à l'exécution) — **fix définitif session 8** : marges ±3600 s ; c'est ce test qui faisait échouer la matrice CI **Drupal 11.2** (runner plus lent : >90 s entre l'évaluation du data provider et l'exécution, la fenêtre d'expiration de 30 s tombait).

### Phase 0 — TERMINÉE (session 8)
- ✅ ESLint durci : no-unused-vars error + import/no-cycle sur packages/next-drupal/src, frontière no-restricted-imports next/* sur next-drupal-base.ts (draft.ts exempté légitimement).
- ✅ **Zéro `any` dans `packages/next-drupal/src`** (8 occurrences converties en `unknown`/génériques explicites) + règle `@typescript-eslint/no-explicit-any: error` activée sur ce périmètre. Au passage : type guard `hasQueryObject` typé dans `buildUrl` (l'ancien code appelait `getQueryObject()` sur un index-signature `any`), générique `JsonApiResourceWithPath` explicite dans `getResourcePreviewUrl`.
- ✅ `turbo.json` : `globalEnv` déclarée (variables DRUPAL_*/NEXT_PUBLIC_* qui affectent les builds → clés de cache correctes).
- ✅ Fork hygiène CI : `next-drupal.yml` (tests d'intégration live avec secrets Chapter Three) ne s'exécute sur push main que sur le repo upstream ; la matrice `next.yml` (D10.5/D10.6/D11.2 × PHP) tourne sur le fork avec le fix D11.2 ci-dessus.
- Référence locale : sources du core Drupal disponibles hors-ligne (`C:\Users\Bastien\Documents\Projects\core-11.x` et `...\drupal-main`, deux checkouts distincts fournis par Bastien) pour toute question de compatibilité API Drupal 10/11.

### Phase 0 (historique)
- ✅ **PHPStan niveau 5** sur `modules/next` (`phpstan.neon`, ignores documentés, vrais bugs corrigés : PHPDoc de collection erroné, type de retour tronqué, `save()` sans return, `accessCheck` déprécié) — intégré à la CI.
- ✅ **`strict: true` complet** activé sur `packages/next-drupal` (les 5 derniers constats corrigés : assertions d'assignation définie, narrowing unknown→Error, état typé useMenu) sur `packages/next-drupal` (tout `src/` y compris helpers deprecated : 0 erreur, types publics honnêtes `T | null`).
- ✅ CI fork `.github/workflows/quality.yml` : prettier + eslint + tsc + jest (env factice) côté JS ; phpcs + PHPUnit (Drupal 10.6 via core-dev-pinned, sqlite + serveur PHP intégré) côté PHP. Recette validée localement (29/29).
- ⏭️ Prochaines étapes : `strictNullChecks` puis `strict: true` ; ESLint durci (`no-unused-vars: error`, `import/no-cycle`, boundary `next/*` dans la classe base) ; PHPStan sur `modules/next`.

### Harnais de test local
- Jest sans Drupal vivant : `cd packages/next-drupal && DRUPAL_BASE_URL=http://localhost DRUPAL_CLIENT_ID=test DRUPAL_CLIENT_SECRET=test npx jest --coverage=false` (référence : 272 passés, 86 échecs réseau préexistants).
- PHPUnit : projet Drupal 10.6 dans `.phpunit-drupal/` (gitignoré) — voir `.agents/skills/next-drupal-dev/SKILL.md`. 35/35 verts.
- Le dossier `drupal/` du dépôt est une install D9 obsolète (copie 1.x du module) : inutilisable pour les tests.

### E2E — état des lieux (Cypress, pas Playwright)
- 8 exemples ont des suites Cypress (`example-marketing`, `example-auth`, `example-custom-*`, `example-search-api`, `example-webform`), lancées par `yarn test:e2e:ci` (start-server-and-test + cypress run).
- **Bloqué sans la base Drupal privée** : les specs assert du contenu faker exact ("Build Something Amazing", etc.) qui n'existe que dans la DB/tests.next-drupal.org détenue par Chapter Three (voir TESTING.md). Le `next-drupal.json`/env des exemples vise ce backend.
- Ce qui est vérifiable sans DB : lint des specs (OK via eslint), build des exemples contre un stub (non implémenté).
- **Levée du blocage — DÉCISION (session 3)** : stratégie à deux temps.
  1. **Court terme (action Bastien)** : demander une copie de la DB tests.next-drupal.org + fichiers à Chapter Three (Slack Drupal `#nextjs`, canal du projet) — c'est le seul chemin qui débloque les specs Cypress **telles quelles** sans engineering. En attendant la réponse, E2E reste hors gates.
  2. **Moyen terme (si refus ou silence sous ~2 semaines)** : lancer le chantier « profil d'install déterministe » — module `next_tests` avec config + contenu seedé, puis réécriture des assertions des specs sur le seed. Autonome, bénéfique pour tout contributeur, mais estimé à plusieurs jours de travail.
- Jusqu'à l'étape 1 ou 2 aboutie, E2E reste hors des gates ; les gates unitaires (Jest ratchet + PHPUnit + phpcs) font foi.

### Dette dépendances dev (post-audit 2026-08-24)
`yarn audit` : 1069 constats (29 critiques), tous dans la toolchain dev des examples/www/starters (node-tar, form-data via cypress 9 / vieux glob, etc.). Les dépendances **runtime du package publié** (jsona, qs 6.15.3, node-cache, next, react) sont propres. Traitement = projet dédié de modernisation (majors cypress 9→14, next 14→15 par exemple), pas des bumps au fil de l'eau.

### Knip (audit dead code) — OPÉRATIONNEL (session 10)
- Config versionnée `knip.json` (workspace `packages/next-drupal`, entry points projetés depuis la carte `exports` : `src/index.ts`, `src/draft.ts`, `src/navigation.ts` — sans ça knip signale des faux positifs car il ne projette pas `dist/` vers `src/`).
- Recette : `npx knip@5 --workspace packages/next-drupal` → **0 finding** : le package publié n'a ni dead code ni dépendances inutiles.
- knip n'est PAS dans le manifest (pas de yarn local pour régénérer yarn.lock ; l'ajouter casserait `--frozen-lockfile` en CI). À intégrer aux devDependencies + CI lors du chantier modernisation. Dette documentée dans `ignoreDependencies` : `@jest/globals` (fourni transitivement par jest, importé tel quel dans les tests) et `dotenv` (utilisé par jest.config.cjs via hoisting racine).
- Constats du scan monorepo complet (non actionnés, examples/www hors gates E2E) : fichiers présumés morts dans `examples/*/cypress/support`, `www/components/doc-search.tsx`, composants link examples ; FAUX POSITIFS connus : `modules/next/css|js/next.site_preview.iframe.*` (chargés via libraries yml, pas d'imports JS). À revoir uniquement si on modernise les examples.

### E2E : chantier next_tests — PIPELINE OPÉRATIONNEL (session 6)
- ✅ **Migration Playwright LIVRÉE (session 29, audit intégral)** : suite Playwright miroir des specs Cypress (`test/e2e/playwright/` + config), `package.json` dédié dans `test/e2e` (indépendant du yarn.lock racine). **Playwright 17/17 ET Cypress 14/14 validés dans la même session (30)** : suite coverage ajoutée (relationships include, fichier média servi, cible du lien de menu, home via frontPage configurable — couverture E2E du fix #158), app e2e dotée d'une home dynamique pilotée par DRUPAL_FRONT_PAGE. Les environnements sans téléchargement navigateur utilisent `PLAYWRIGHT_CHANNEL=msedge`. **La CI E2E est LIVE depuis la session 31** : `.github/workflows/e2e.yml` installe le site seedé, démarre les serveurs et exécute les 17 tests Playwright à chaque push/PR (Node 22 pour ce job — le pin 18.19 reste réservé à Jest).
- ✅ Message Slack prêt à poster pour la DB Chapter Three : `docs/SLACK-DB-REQUEST.md` (action Bastien, Slack #nextjs).
- ✅ Décision (session 28) : **poursuite du seed déterministe** prioritaire sur le chantier moderne — la migration Playwright n'aurait pas de sens avant que les specs legacy soient réécrites sur un contenu déterministe ; le seed est la seule voie autonome.
- ✅ Seed étendu au pattern « article » (type next_test_article + body + référence image média + référence tags taxonomy, vocabulaire next_test_tags, terme « Next tests tag », article « Next tests article » alias /next-tests/article) — le cœur des examples marketing/blog est désormais rejouable. **Cypress 13/13** (3 nouvelles specs : collection article, terme, rendu Next).
- ✅ **Cypress 3/3 specs PASSENT** contre un site Drupal local seedé (JSON:API index, contenu déterministe, decoupled router) — exécution réelle, sans DB Chapter Three.
- ✅ Script reproductible `test/e2e/install-drupal.sh` + doc `test/e2e/README.md` (pièges documentés : chemin sqlite ≤128, purge conteneur périmé, extraction binaire cypress).
- ✅ **Specs seed-features (révisions + menu) passantes** : UUIDs 36 chars corrects, 2e révision, lien de menu, permission de révision anonyme — **9/9 specs cypress**.
- ✅ **Specs de RENDU Next.js passantes** : app  (workspace lié à next-drupal local) rend les pages seedées via translatePath + getResource — **6/6 specs cypress** (JSON:API + rendu + 404).
- ⏭️ Suite : enrichir le seed (menus, médias, révisions) et étendre les specs, puis réécrire les specs faker historiques sur ce socle.
- La demande de DB Chapter Three reste pertinente pour les specs legacy (action Bastien, Slack #nextjs).
- ✅ Module seed déterministe `modules/next/tests/modules/next_tests_seed` (type dédié + pages à titres/alias fixes) avec test kernel (32/32).
- ⏭️ Suite : script d'installation de site complet (drush site:install + next + seed + config NextSite), puis specs Cypress smoke contre le seed, puis réécriture progressive des specs faker.
- La demande de DB Chapter Three reste le raccourci (action Bastien, Slack #nextjs).

### strictNullChecks — TERMINÉ (session 3)
Activé package-wide avec `noImplicitAny` : 0 erreur sur tout `src/` (y compris helpers deprecated). Types publics rendus honnêtes (`T | null`, `tree?: DrupalMenuTree`, ids numériques). Prochaine étape Phase 0 : `strict: true` complet (restent principalement `strictFunctionTypes`/`strictBindCallApply`), puis ESLint durci et PHPStan.

---

## 0. Retour sur la session précédente (2026-08-24)

### Réalisé et vérifié
- **Environnement** : dépendances installées (yarn 1.22.15), environnement de test unitaire opérationnel sans Drupal vivant (`DRUPAL_BASE_URL=http://localhost DRUPAL_CLIENT_ID=test DRUPAL_CLIENT_SECRET=test npx jest <fichier> --coverage=false`).
- **Correctifs commités sur branches locales** :
  - `fix/854-add-locale-prefix` (commit `49dd523c`) — `addLocalePrefix()` ne préfixait plus `/enable/...` pour la locale `en`. 3 nouveaux tests, 44 tests OK, tsc OK.
  - `fix/499-drupal-node-promote` (commit `394eccb9`) — champ `promote` ajouté au type `DrupalNode`. tsc OK.
- **Documentation IA créée** : `AGENTS.md` + 3 skills projet (`.agents/skills/next-drupal-dev`, `next-drupal-conventions`, `next-drupal-triage`), commités sur `main` du fork (`0efa1533`) et poussés.
- **Skills Drupal officiels installés** : `drupal-coding-standards`, `drupal-contribute-fix`, `drupal-issue-queue` (via `npx skills add`, verrouillés dans `skills-lock.json`).
- **Analyse complète** : 169 issues ouvertes et 50 PR ouvertes d'upstream classées ; audit des conventions réalisé.

### Constats de l'audit (point de départ)
| Constat | Détail |
|---|---|
| TypeScript **non strict** | `strict: false` à la racine — incompatible avec l'objectif « 100% typesafe » |
| `any` quasi absent | Bonne nouvelle : seulement `types/resource.ts` et `deprecated/` |
| Encapsulation absente | Aucun `private`/`protected` — convention historique du dépôt |
| POO présente et saine | `NextDrupalBase` → `NextDrupal` → `NextDrupalPages`, `JsonApiErrors extends Error`, `DrupalMenuTree<T>` |
| Conventional commits réellement appliqués | commitlint + husky + lint-staged |
| Couverture 100% imposée côté JS | `jest.config.cjs` (seuils globaux) — à préserver |
| Dettes côté PR upstream | 22 PR dependabot, 14 PR en conflit, ~28 PR substantielles en attente depuis 2024-2025 |
| Dettes côté issues upstream | 60 en `triage`, ~55 sans label |

### Non fait / annulé
- Ouverture des 2 PR vers upstream : **annulé sur décision de Bastien** (le fork `next-drupal-contribute` créé au passage reste inutilisé, à supprimer éventuellement).
- Publication de revues sur les PR upstream : annulé (même décision).

---

## 1. Principes directeurs (standards demandés, adaptés à CE projet)

Légende : ✅ déjà en place · 🔧 à mettre en place · ⏸️ plus tard · ❌ hors périmètre (lib OSS, pas une app exploitée).

### 1.1 Conception & architecture
| Principe | Application concrète ici | Statut |
|---|---|---|
| **SOLID** | Un client par responsabilité : Base (auth/URL/CRUD) / App Router / Pages Router. Toute nouvelle capacité va dans la bonne classe, pas « où c'est pratique ». | 🔧 codifier |
| **KISS / YAGNI / DRY** | Pas de refactor big-bang : améliorations incrémentales, une branche = un sujet. | 🔦 règle continue |
| **POO + design patterns** | Conserver l'héritage existant ; patterns déjà présents à nommer et documenter (Adapter pour `fetcher`/`serializer` injectables, Options Object, Error typée). Aucun pattern gratuit. | 🔧 documenter dans le skill conventions |
| **Clean Architecture (adaptée)** | Séparer ce qui est « cœur métier » (construction URL JSON:API, désérialisation, arbres de menus) du « détail framework » (Next.js). Interdiction d'importer `next/*` dans `next-drupal-base.ts` (déjà le cas — à verrouiller par lint). | 🔧 règle lint boundary |
| **DDD (léger)** | Le langage métier = Drupal (resources, bundles, revisions, locales, revalidation). Nommer le code avec ce vocabulaire, pas celui de Next. | 🔦 règle continue |
| **100% typesafe** | Migration TS `strict: true` par étapes (voir Phase 0). Zéro `any` nouveau ; types précis dans `src/types/`. | 🔧 priorité maximale |

### 1.2 i18n & zéro hardcodage (exigence forte de Bastien)
| Règle | Détail |
|---|---|
| **Aucun texte utilisateur codé en dur** | Dans starters/examples/www : tout libellé vient de Drupal (contenu JSON:API ou config). Les chaînes de la lib elle-même restent techniques (messages d'erreur destinés aux dev) et en anglais. |
| **Aucune couleur / style codé en dur** | Design tokens et variantes passent par Drupal (config) ou par des tokens du thème — jamais un hex figé dans un composant métier. |
| **i18n de bout en bout** | Les chemins gèrent les locales (fix #854 déjà en place côté lib), les contenus et libellés sont traduits côté Drupal (module locale/translation), Next ne fait que consommer. Toute nouvelle fonction de la lib doit accepter `locale`/`defaultLocale`. |
| **Config externalisée** | 12-Factor : secrets et URLs via variables d'env uniquement (déjà le cas : `DRUPAL_*`). Jamais de secret en dur (le scanning CI le vérifiera). |

### 1.3 Tests & qualité
| Pratique | Application | Statut |
|---|---|---|
| Pyramide de tests | Unitaires (majoritaires, sans réseau) → intégration (mock fetch) → E2E Cypress (existant, nécessite Drupal). Tout nouveau code `src/` = test unitaire (couverture 100% déjà imposée). | ✅/🔧 étendre les tests sans Drupal |
| Linters stricts | ESLint : passer `no-unused-vars` en erreur, ajouter `no-restricted-imports` (interdire `next/*` dans la base), `import/no-cycle`. Prettier déjà en place. | 🔧 Phase 0 |
| Analyse statique PHP | PHPStan (niveau max visé, progressif) sur `modules/next` + phpcs (déjà là, standards Drupal). | 🔧 Phase 0 |
| Complexité | Limite de complexité cyclomatique ESLint sur `packages/next-drupal`. | ⏸️ |
| Mutation testing, contrat, charge | | ❌/⏸️ hors périmètre immédiat |

### 1.4 Collaboration & livraison
| Pratique | Application | Statut |
|---|---|---|
| Conventional Commits | Déjà enforced (commitlint). Scopes = dossiers (`next-drupal`, `next`, `basic-starter`…). | ✅ |
| SemVer | Géré par Lerna sur les versions du package. Sur le fork : versionner nos séries en `-bastien.N` si besoin sans publier. | 🔧 à définir |
| Trunk-based léger | `main` du fork = intégration continue ; branches courtes `fix/…`, `feat/…` fusionnées vite. | 🔦 |
| PR < 400 lignes | Règle de revue pour nos propres PR internes (le ménage upstream se fait par petits cherry-picks attribués). | 🔦 |
| CI/CD | GitHub Actions du fork : lint + typecheck + tests unitaires à chaque push (sans secrets). | 🔧 Phase 0 |
| ADR | `docs/adr/` : chaque décision structurante = un fichier court (contexte, décision, conséquences). | 🔧 Phase 0 |
| DoR/DoD | Voir §6 Gouvernance. | 🔧 |
| Dependabot | On bump nous-mêmes nos dépendances (pas de reprise des 22 PR upstream). | 🔦 |

### 1.5 Explicitement hors périmètre (documenté pour ne pas y revenir)
Kubernetes/GitOps, IaC, chaos engineering, microservices/CQRS/Event Sourcing, SLI/SLO, feature flags managés, RGPD (rien de personnel stocké). Ces standards s'appliquent à des produits exploités ; ici c'est une bibliothèque OSS + modules. Si le projet devient une plateforme, on rouvrira cette section.

---

## 2. Phase 0 — Fondations qualité (à faire en premier)

1. **TypeScript strict par étapes** : `noImplicitAny` → `strictNullChecks` → `strict: true`, en corrigeant à chaque étape. Vérifier que `tsc --noEmit` et tsup passent à chaque pas.
2. **ESLint durci** : `no-unused-vars: error` (TS), `no-restricted-imports` (couche base), `import/no-cycle`, complexité modérée.
3. **PHPStan** sur `modules/next` (niveau initial 5, montée progressive) ; garder phpcs.
4. **CI fork** : workflow `quality.yml` — prettier check + eslint + tsc + jest unitaires (sans Drupal) sur push/PR du fork.
5. **Gouvernance** : `docs/adr/0001-strict-typescript.md`, `0002-politique-amont.md` (on n'ouvre rien vers upstream), `0003-adoption-pr-upstream.md` (politique de cherry-pick attribué).
6. **AGENTS.md** enrichi des skills Drupal + règles i18n/zéro hardcodage (fait dans cette session, à maintenir).

## 3. Phase 1 — Ménage des 50 PR upstream

Stratégie : **adopter sur notre fork** les bonnes contributions (cherry-pick/rebase + attribution `Co-authored-by:`), ignorer le reste. Rien n'est fusionné côté upstream.

**À adopter (ordre de priorité)** :
1. #865 (403→404 contenu archivé), #790 + #791 (getParams), #842 (docs getResource), #904 (docs branches) — petits et sûrs.
2. #853 (draft/SSG), #844 (revalider l'alias), #876 (workspace preview), #846 (AJAX form) — à valider par tests chez nous.
3. #856 (host header) — à réimplémenter en option opt-in propre (pas d'import `next/headers` dans le client).
4. #836 (Umami à jour), #747, #306, #584, #67, #425, #446, #491 — au cas par cas selon pertinence.

**À ignorer** : 22 PR dependabot (on gère nos bumps), PR en conflit périmées de 2024 (#785, #795, #758, #715, #703, #762…).

## 4. Phase 2 — Résolution des 169 issues, par vagues thématiques

| Vague | Thème | Issues phares | Notes |
|---|---|---|---|
| 1 | **i18n / locales** | #854 ✅(fait), #861, #912, #854-liés | Extension naturelle de notre fix |
| 2 | **Preview / draft mode** | #847, #852, #859, #875, #867, #855 | Le sujet n°1 des users v2 |
| 3 | **Revalidation / cache** | #843, #862, #886, #911, #848 | Inclut l'adoption de #844 |
| 4 | **Bugs isolés** | #874, #849, #857, #850, #854 | Chaque fix = test unitaire |
| 5 | **Questions / docs** | issues `question` (22) | Répondre, labelliser, fermer si résolu |

Règle par issue : reproduire (test rouge) → corriger → test vert → coverage maintenue à 100% → commit conventionnel `fix(next-drupal): ... Fixes #NNN`.

## 5. Phase 3 — Architecture cible « tout piloté depuis Drupal »

1. **Audit zéro-hardcodage** des starters/examples/www : lister textes, couleurs, configs codés en dur ; les faire venir de Drupal (config entities du module `next`, contenu JSON:API).
2. **i18n complet** : propager `locale`/`defaultLocale` dans toutes les API de la lib ; documenter le pattern de traduction côté contenu.
3. **Module `next` en POO propre** : s'appuyer sur les plugins Drupal existants (SitePreviewer, Revalidator, SiteResolver) — chaque extension = un plugin, jamais un `if` ajouté au cœur.
4. **Types générés (exploration)** : génération de types TS depuis les définitions d'entités Drupal (custom codegen ou outil existant) pour tendre vers le « typesafe 100% » bout en bout.
5. **ADR + skills à jour** à chaque étape.

## 6. Gouvernance

**Definition of Ready (une issue entre en travail si)** : reproduisible ou spécifiée clairement ; rattachée à une vague ; pas de doublon ouvert.

**Definition of Done (une branche est finie si)** :
- [ ] tests unitaires verts, couverture globale ≥ 100% maintenue
- [ ] `tsc --noEmit` strict OK (dès que Phase 0.1 faite)
- [ ] eslint + prettier OK
- [ ] commit conventionnel avec scope + `Fixes #NNN`
- [ ] aucun texte/couleur codé en dur introduit
- [ ] doc/skill mise à jour si comportement public change
- [ ] rien poussé vers upstream

**Traçabilité** : chaque décision structurante = ADR numéroté dans `docs/adr/`. Chaque adoption de PR upstream = attribution de l'auteur d'origine dans le commit.
