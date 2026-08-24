# Demande : copie de la DB tests.next-drupal.org

Message prêt à poster sur le Slack Drupal, canal **#nextjs**, à destination de
Chapter Three (mainteneurs de next-drupal). Copier-coller tel quel.

---

Hi Chapter Three team 👋

I'm working on a fork of next-drupal (github.com/BourezBastien/next-drupal) and I'd love to run the historical Cypress suites that ship in `examples/*` (example-marketing, example-blog, example-auth, example-search-api, example-webform, example-custom-*) as-is.

As documented in TESTING.md, those specs assert faker-generated content ("Build Something Amazing", etc.) that only exists in the tests.next-drupal.org database. Would you be willing to share a copy (or sanitized dump) of that database + files directory so the suites can be replayed locally?

Happy to sign whatever is needed, and the dump would only be used for local test runs — never redistributed. If a copy isn't possible, no worries: I'll keep extending the deterministic seed module (`next_tests_seed`) to cover the specs.

Thanks for the great project!

---

## Pourquoi c'est le seul débloqueur

- Les 8 examples ont des suites Cypress qui assertent **verbatim** des chaînes
  faker présentes uniquement dans la DB privée.
- Sans elle, la couverture passe par le module seed déterministe
  (`modules/next/tests/modules/next_tests_seed`), qui recrée le contenu attendu
  de façon reproductible (pipeline `test/e2e`).
- La migration Cypress → Playwright est planifiée **après** la réécriture des
  specs sur le seed (voir docs/PLAN.md) : migrer l'outil avant le contenu
  n'apporterait rien.
