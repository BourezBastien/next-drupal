#!/usr/bin/env node

/**
 * Scaffolds a new Next.js for Drupal project from one of the starters in
 * this repository. Zero npm dependencies: plain Node (>= 18) and git.
 *
 * Usage:
 *   create-drupal-app <path> [options]
 *
 * Options:
 *   --starter <name>    Starter to use (default: basic).
 *   --repo <owner/name> Repository to fetch the starter from
 *                       (default: BourezBastien/next-drupal).
 *   --branch <name>     Branch to fetch from (default: main).
 *   --drupal-url <url>  Prefills NEXT_PUBLIC_DRUPAL_BASE_URL in .env.local.
 *   --skip-git          Do not initialize a git repository.
 *   -h, --help          Show this help.
 */

"use strict"

const { execFileSync } = require("child_process")
const fs = require("fs")
const os = require("os")
const path = require("path")

const DEFAULT_REPO = "BourezBastien/next-drupal"
const DEFAULT_BRANCH = "main"
const DEFAULT_STARTER = "basic"

function parseArgs(argv) {
  const options = {
    target: undefined,
    starter: DEFAULT_STARTER,
    repo: DEFAULT_REPO,
    branch: DEFAULT_BRANCH,
    drupalUrl: undefined,
    skipGit: false,
    help: false,
  }

  for (let i = 0; i < argv.length; i++) {
    const arg = argv[i]
    switch (arg) {
      case "-h":
      case "--help":
        options.help = true
        break
      case "--starter":
        options.starter = argv[++i]
        break
      case "--repo":
        options.repo = argv[++i]
        break
      case "--branch":
        options.branch = argv[++i]
        break
      case "--drupal-url":
        options.drupalUrl = argv[++i]
        break
      case "--skip-git":
        options.skipGit = true
        break
      default:
        if (arg.startsWith("--")) {
          fail(`Unknown option: ${arg}`)
        }
        if (options.target) {
          fail("Only one target path is allowed.")
        }
        options.target = arg
    }
  }

  return options
}

function fail(message) {
  console.error(`Error: ${message}`)
  process.exit(1)
}

function run(cmd, args, opts) {
  return execFileSync(cmd, args, { stdio: "pipe", ...opts })
    .toString()
    .trim()
}

function help() {
  console.log(
    [
      "Usage: create-drupal-app <path> [options]",
      "",
      "Options:",
      "  --starter <name>     Starter to use (default: basic).",
      "  --repo <owner/name>  Repository to fetch from (default: BourezBastien/next-drupal).",
      "  --branch <name>      Branch to fetch from (default: main).",
      "  --drupal-url <url>   Prefills NEXT_PUBLIC_DRUPAL_BASE_URL in .env.local.",
      "  --skip-git           Do not initialize a git repository.",
      "  -h, --help           Show this help.",
    ].join("\n")
  )
}

function scaffold(options) {
  const target = path.resolve(options.target)
  const projectName = path.basename(target)

  if (fs.existsSync(target) && fs.readdirSync(target).length > 0) {
    fail(`The target directory is not empty: ${target}`)
  }

  const starterPath = `starters/${options.starter}-starter`
  console.log(`Fetching ${options.repo}@${options.branch} (${starterPath})...`)

  const tmp = fs.mkdtempSync(path.join(os.tmpdir(), "create-drupal-app-"))
  try {
    run("git", [
      "clone",
      "--depth",
      "1",
      "--filter=blob:none",
      "--sparse",
      `https://github.com/${options.repo}`,
      tmp,
    ])
    run("git", ["sparse-checkout", "set", starterPath], { cwd: tmp })

    const source = path.join(tmp, starterPath)
    if (!fs.existsSync(path.join(source, "package.json"))) {
      fail(
        `Starter not found: ${starterPath} (in ${options.repo}@${options.branch}).`
      )
    }

    fs.mkdirSync(target, { recursive: true })
    fs.cpSync(source, target, { recursive: true, dotfiles: true })
    console.log(
      `Scaffolded ${projectName} from the ${options.starter} starter.`
    )
  } finally {
    fs.rmSync(tmp, { recursive: true, force: true })
  }

  // Rename the project in package.json.
  const pkgPath = path.join(target, "package.json")
  const pkg = JSON.parse(fs.readFileSync(pkgPath, "utf8"))
  pkg.name = projectName
  fs.writeFileSync(pkgPath, `${JSON.stringify(pkg, null, 2)}\n`)

  // Local environment file from the template, with an optional Drupal URL.
  const envExample = path.join(target, ".env.example")
  if (fs.existsSync(envExample)) {
    let env = fs.readFileSync(envExample, "utf8")
    if (options.drupalUrl) {
      env = env.replace(
        /^NEXT_PUBLIC_DRUPAL_BASE_URL=.*$/m,
        `NEXT_PUBLIC_DRUPAL_BASE_URL=${options.drupalUrl}`
      )
    }
    fs.writeFileSync(path.join(target, ".env.local"), env)
    console.log("Created .env.local from the template.")
  }

  if (!options.skipGit) {
    run("git", ["init", "--quiet"], { cwd: target })
    console.log("Initialized a git repository.")
  }

  console.log("")
  console.log("Next steps:")
  console.log(`  cd ${path.relative(process.cwd(), target) || "."}`)
  console.log("  npm install")
  if (!options.drupalUrl) {
    console.log("  # Edit .env.local: set NEXT_PUBLIC_DRUPAL_BASE_URL")
  }
  console.log("  # Drupal side: install the next module and add your site")
  console.log("  # https://next-drupal.org/docs/quick-start")
  console.log("  npm run dev")
}

function main() {
  const options = parseArgs(process.argv.slice(2))
  if (options.help || !options.target) {
    help()
    process.exit(options.help ? 0 : 1)
  }
  scaffold(options)
}

main()
