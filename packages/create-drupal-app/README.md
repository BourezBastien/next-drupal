# create-drupal-app

Scaffolds a new **Next.js for Drupal** project from the starters of this
repository — with zero npm dependencies (plain Node ≥ 18 and git).

## Usage

From the repository (before an eventual npm publication):

```sh
node packages/create-drupal-app/bin/create-drupal-app.js my-site
```

Once published to npm (name reservation and `npm publish` from this
directory), it becomes:

```sh
npx create-drupal-app my-site
```

## Options

| Option                | Description                                            | Default                     |
| --------------------- | ------------------------------------------------------ | --------------------------- |
| `--starter <name>`    | Starter to scaffold (`basic`)                          | `basic`                     |
| `--repo <owner/name>` | Repository to fetch the starter from                   | `BourezBastien/next-drupal` |
| `--branch <name>`     | Branch to fetch from                                   | `main`                      |
| `--drupal-url <url>`  | Prefills `NEXT_PUBLIC_DRUPAL_BASE_URL` in `.env.local` | —                           |
| `--skip-git`          | Do not initialize a git repository                     | —                           |

## What it does

1. Sparse-clones the repository (only the requested starter is fetched).
2. Copies the starter to the target directory (must be empty or new).
3. Renames the project in `package.json` after the target directory.
4. Creates `.env.local` from the starter's `.env.example`, prefilled with
   `--drupal-url` when given.
5. Initializes a git repository (unless `--skip-git`).
6. Prints the next steps (install, Drupal-side setup, `npm run dev`).

The scaffolded project is fully standalone: it depends on the published
`next-drupal` package from npm, not on this monorepo.

## Adding starters

Add a new directory under `starters/<name>-starter` and it becomes available
through `--starter <name>` automatically.
