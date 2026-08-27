# Deploying to production

How this project goes from `git push` on `branch_1x` (or `main`, once
merged) to a real domain on real hosting. Read in order the first time you
deploy; after that, `github-ci-cd.md` is the only one you'll touch
regularly.

1. **[hosting-requirements.md](./hosting-requirements.md)**
   — what kind of hosting to buy and what it needs to support (PHP
   extensions, MySQL) to run the same stack the Dockerfile builds locally.
2. **[domain-dns.md](./domain-dns.md)** — pointing a domain
   you own at that hosting, and HTTPS.
3. **[initial-deploy.md](./initial-deploy.md)** — the one-time setup:
   database, WordPress core, this repo's `wp-content`, `wp-config.php`, the
   install wizard.
4. **[github-ci-cd.md](./github-ci-cd.md)** — how every future
   `git push` reaches the live site. Two options depending on what your
   hosting plan gives you (SSH or FTP-only), and — importantly — exactly
   what does and doesn't get touched by a sync.

## The one thing to keep in mind throughout

Same rule as local dev (see the root `CLAUDE.md`): **this repo only ever
contains `public/wp-content/mu-plugins/` and
`public/wp-content/themes/minimal-reader/`.** WordPress core, the database,
and `wp-content/uploads/` are not in git on purpose — locally the
`Dockerfile` downloads core fresh and MySQL runs in its own container; in
production, core is installed once directly on the host and the database
lives in the hosting provider's MySQL. Deploying is never "copy the whole
repo over the live site" — it's always "install/keep WordPress core in
place, and layer this repo's `wp-content` on top of it," the same additive
merge the Dockerfile already does for local dev.
