# Syncing from GitHub to production

Every deploy — including the first one in `wp-initial-setup.md` — is the
same operation: get the current `public/wp-content/mu-plugins/` and
`public/wp-content/themes/minimal-reader/` from this repo onto the live
server, replacing whatever's there. Nothing else in the repo ever goes to
the server, and nothing on the server outside those two folders is ever
touched by a sync.

## Which branch actually deploys

This repo's branch model (see `README.md`): `branch_1x` is where active
work happens — pushed to constantly, including half-finished experiments —
and `main` is production, only ever updated by merging a reviewed
`branch_1x` (via a PR, the same moment `CHANGELOG.md` gets a version cut).

**The CI/CD workflow below triggers only on a push to `main`.** A push to
`branch_1x` never touches the live site, by design — that branch is for
building and testing locally (Docker), not for the production server to
see. The live site only ever reflects whatever was last merged into `main`.
If you ever see the workflow trigger on the wrong branch, that's a bug in
the workflow file, not intended behavior.

Optional but worth it once this is live: **Settings → Branches → add a
protection rule for `main`** requiring a pull request before merging.
Nothing here technically depends on it, but it turns "only a reviewed
merge updates `main`" from a convention into something GitHub actually
enforces, matching the "cut a version at merge time" workflow
`CHANGELOG.md` already describes.

## What never gets touched by a sync — read this before setting anything up

- `wp-config.php` — hand-created once in `wp-initial-setup.md`, holds real
  DB credentials and salts, never in git.
- WordPress core (`wp-admin/`, `wp-includes/`, root PHP files) — installed
  once, updated only through wp-admin's own Dashboard → Updates.
- `wp-content/uploads/` — real media, never in git.
- `wp-content/plugins/`, other themes — not this repo's concern; also
  irrelevant in practice since `DISALLOW_FILE_MODS` (set in
  `wp-initial-setup.md`) prevents installing anything there from
  wp-admin anyway.
- The database — content lives there, not in git (see `README.md`'s
  Content Strategy section).

This is why a sync is always scoped to exactly two subdirectories, never a
wholesale copy of `wp-content/` or the document root. **Never point a
`--delete`-flagged sync, or an FTP client's "mirror" mode, at anything
broader than `wp-content/mu-plugins/` and `wp-content/themes/minimal-reader/`
specifically** — pointed at `wp-content/` itself, `--delete` would erase
`uploads/` along with everything else that isn't in git.

## Path A — the hosting plan has SSH access

### Manual (do this first — automate once this works)

From your own machine, with the server's SSH details:

```bash
rsync -avz --delete \
  public/wp-content/mu-plugins/ \
  youruser@yourserver:~/public_html/wp-content/mu-plugins/

rsync -avz --delete \
  public/wp-content/themes/minimal-reader/ \
  youruser@yourserver:~/public_html/wp-content/themes/minimal-reader/
```

Run after every `git pull` locally that you want reflected live — or wire
it into a small script:

```bash
#!/bin/bash
set -e
cd "$(git rev-parse --show-toplevel)"
git pull origin branch_1x   # or main, once merged

REMOTE=youruser@yourserver
REMOTE_PATH=~/public_html/wp-content

rsync -avz --delete public/wp-content/mu-plugins/      "$REMOTE:$REMOTE_PATH/mu-plugins/"
rsync -avz --delete public/wp-content/themes/minimal-reader/ "$REMOTE:$REMOTE_PATH/themes/minimal-reader/"
```

`--delete` is safe here specifically because each `rsync` targets exactly
one of the two mirrored subdirectories — a file removed from
`mu-plugins/custom-x/` in git correctly disappears on the server too,
without going anywhere near `uploads/` or anything else in `wp-content/`.

### Automating it with GitHub Actions (recommended once the manual version works)

A workflow that rsyncs straight from the CI runner to the server over SSH
on every push — the server doesn't need git installed at all, only SSH.

`.github/workflows/deploy.yml`:

```yaml
name: Deploy to production

on:
  push:
    branches: [main]   # production only — see "Which branch actually deploys" above

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Deploy mu-plugins
        uses: easingthemes/ssh-deploy@main
        with:
          SSH_PRIVATE_KEY: ${{ secrets.DEPLOY_SSH_KEY }}
          REMOTE_HOST: ${{ secrets.DEPLOY_HOST }}
          REMOTE_USER: ${{ secrets.DEPLOY_USER }}
          SOURCE: "public/wp-content/mu-plugins/"
          TARGET: ${{ secrets.DEPLOY_MU_PLUGINS_PATH }}
          ARGS: "-avz --delete"

      - name: Deploy theme
        uses: easingthemes/ssh-deploy@main
        with:
          SSH_PRIVATE_KEY: ${{ secrets.DEPLOY_SSH_KEY }}
          REMOTE_HOST: ${{ secrets.DEPLOY_HOST }}
          REMOTE_USER: ${{ secrets.DEPLOY_USER }}
          SOURCE: "public/wp-content/themes/minimal-reader/"
          TARGET: ${{ secrets.DEPLOY_THEME_PATH }}
          ARGS: "-avz --delete"
```

This is the actual file at `.github/workflows/deploy.yml` in this repo —
not just a doc example. It won't run successfully until the secrets below
are set, which is expected and harmless (a failed Action doesn't touch the
live site).

Set six secrets under the repo's **Settings → Secrets and variables →
Actions**:

| Secret | Value |
|---|---|
| `DEPLOY_SSH_KEY` | The **private** half of a dedicated deploy keypair — generate with `ssh-keygen -t ed25519 -C "github-actions-deploy"` (don't reuse your personal key). Never commit either half. |
| `DEPLOY_HOST` | The server's hostname or IP. |
| `DEPLOY_USER` | The SSH/hosting account username. |
| `DEPLOY_MU_PLUGINS_PATH` | The absolute path to `wp-content/mu-plugins/` on the live server, trailing slash included. |
| `DEPLOY_THEME_PATH` | The absolute path to `wp-content/themes/minimal-reader/` on the live server, trailing slash included. |

The public half of that same keypair goes on the server — on 1Panel that's
Advanced Features → SSH Manager → Import SSH Key (see
`inet-onepanel-setup.md`); on most other panels it's appending to
`~/.ssh/authorized_keys` directly. The two path secrets exist specifically
so this workflow file never has to hardcode (or guess at) this hosting
account's actual directory layout — same principle as `docker/.env`
already being gitignored locally: deploy-specific configuration is a
secret, never something committed as code.

## Path B — FTP/SFTP only, no SSH

Still automatable from GitHub Actions, just via FTP instead of rsync-over-SSH:

```yaml
name: Deploy to production

on:
  push:
    branches: [main]

jobs:
  deploy-mu-plugins:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.DEPLOY_HOST }}
          username: ${{ secrets.DEPLOY_USER }}
          password: ${{ secrets.DEPLOY_PASSWORD }}
          protocol: ftps
          local-dir: public/wp-content/mu-plugins/
          server-dir: /public_html/wp-content/mu-plugins/

  deploy-theme:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.DEPLOY_HOST }}
          username: ${{ secrets.DEPLOY_USER }}
          password: ${{ secrets.DEPLOY_PASSWORD }}
          protocol: ftps
          local-dir: public/wp-content/themes/minimal-reader/
          server-dir: /public_html/wp-content/themes/minimal-reader/
```

Use `ftps` (FTP over TLS), not plain `ftp`, so the password isn't sent in
the clear — virtually every host supports it. Same secrets setup as
Path A.

### Fully manual fallback (no GitHub Actions at all)

If automating isn't worth it yet: after pulling locally, connect with an
FTP client (FileZilla, Cyberduck, or cPanel's File Manager) and upload the
contents of `public/wp-content/mu-plugins/` and
`public/wp-content/themes/minimal-reader/` over their matching folders on
the server, replacing existing files. Slower and easier to get wrong (easy
to miss a deleted file, since FTP clients don't mirror-delete by default)
but works with nothing but hosting credentials.

## After any sync

If a plugin added new functionality that needs activation-time setup
(a new option, a rewrite rule), that's the same one-time step on production
as it was locally — check that plugin's `docs/plugins/*.md`. Routine code
updates need nothing further; MU-plugins load automatically and there's no
"activate" step.
