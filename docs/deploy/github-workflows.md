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
wholesale copy of `wp-content/` or the document root.

## This project's actual method: FTPS — SSH does not work on this host

**Confirmed, the hard way, during the real first deploy attempts (see
`CHANGELOG.md`'s v1.0.3): this hosting plan's port 22 refuses connections
from outside its own panel, full stop.** Tested two independent ways —
a GitHub Actions run attempting to `rsync` in, and a direct `ssh -v` from a
developer's own machine — both got `Connection refused` immediately (not a
timeout, meaning nothing external even completes a TCP handshake on that
port). The only SSH that works on this plan is 1Panel's own built-in Web
Terminal, which isn't an externally-reachable SSH service and can't be
used as one for CI. See `inet-onepanel-setup.md`'s "Confirmed for the
WP-H1 tier specifically" section for the full story, including why
**Advanced Features → SSH Manager → Import SSH Key is also not the way**
to authorize an external key (that form wants a private key too, because
it's for 1Panel's own outbound SSH use, not inbound).

**Net result: this project deploys over FTPS, not SSH/rsync.** The
SSH/rsync method below is kept in this doc purely as general-purpose
reference (it's the better approach on a host that actually supports it)
— skip straight to "Deploying via FTPS" for how this project's real
pipeline works.

## Deploying via FTPS (this project's real method)

### 1. Create a dedicated FTP account

1Panel → **Website Management → FTP Account** → create a new account.
When asked for the account's **root directory**, point it at
`public_html` (the document root) — this project's FTP paths below
assume that root.

Write down the FTP **username** and **password** it's given (or that you
set) — these become two of the GitHub secrets below. The username is
separate from, and unrelated to, the SSH/panel login username.

### 2. The workflow file

This is the actual file at `.github/workflows/deploy.yml` in this repo —
not just a doc example:

```yaml
name: Deploy to production

on:
  push:
    branches: [main]   # production only — see "Which branch actually deploys" above

jobs:
  deploy-mu-plugins:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Deploy mu-plugins
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.DEPLOY_HOST }}
          username: ${{ secrets.DEPLOY_USER }}
          password: ${{ secrets.DEPLOY_PASSWORD }}
          protocol: ftps
          local-dir: public/wp-content/mu-plugins/
          server-dir: ${{ secrets.DEPLOY_MU_PLUGINS_PATH }}

  deploy-theme:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Deploy theme
        uses: SamKirkland/FTP-Deploy-Action@v4.3.5
        with:
          server: ${{ secrets.DEPLOY_HOST }}
          username: ${{ secrets.DEPLOY_USER }}
          password: ${{ secrets.DEPLOY_PASSWORD }}
          protocol: ftps
          local-dir: public/wp-content/themes/minimal-reader/
          server-dir: ${{ secrets.DEPLOY_THEME_PATH }}
```

`protocol: ftps` (FTP over TLS), never plain `ftp` — so the password isn't
sent in the clear. Two separate jobs (not two steps in one job) so a
failure in one doesn't block the other, and so each job's log is scoped to
exactly one folder when debugging.

### 3. Set five secrets

Under the repo's **Settings → Secrets and variables → Actions**:

| Secret | Value | Notes |
|---|---|---|
| `DEPLOY_HOST` | Server's public IP or hostname. | On this plan, found by running `curl -s ifconfig.me` in 1Panel's Web Terminal — don't use `hostname -I`, which can return an internal/NAT'd address instead of the real public one. |
| `DEPLOY_USER` | The **FTP account's** username (step 1) — not the SSH/panel login. | |
| `DEPLOY_PASSWORD` | The FTP account's password. | New secret this method needs that SSH-based deploy didn't. |
| `DEPLOY_MU_PLUGINS_PATH` | Path to `wp-content/mu-plugins/`, **relative to the FTP account's own root** (not an absolute filesystem path). | With root set to `public_html` (step 1), this is `wp-content/mu-plugins/`. |
| `DEPLOY_THEME_PATH` | Path to `wp-content/themes/minimal-reader/`, same relative rule. | `wp-content/themes/minimal-reader/`. |

**This is the one real gotcha versus the SSH method**: FTP paths are
relative to wherever the FTP account's root was set at creation, while
SSH/rsync paths are absolute filesystem paths (e.g. found via `cd
~/public_html/wp-content/mu-plugins && pwd` on the SSH method). Don't
reuse absolute SSH-style paths here — they won't resolve correctly through
an FTP root that's already scoped to `public_html`.

Setting a secret, one value at a time, from your own terminal:

```bash
echo -n "the value" | gh secret set SECRET_NAME
```

(`gh secret list` afterward confirms all five names are present — it never
shows values back, by design.)

### 4. Verify

```bash
gh run list --limit 3        # see the latest "Deploy to production" run and its status
gh run view <run-id> --log-failed   # read the actual error if it failed
```

A clean run shows both `deploy-mu-plugins` and `deploy-theme` jobs
succeeding. If `DEPLOY_SSH_KEY` still exists as a secret from an earlier
attempt at the SSH method, it's unused now and safe to delete
(`gh secret delete DEPLOY_SSH_KEY`).

### Fully manual fallback (no GitHub Actions at all)

If you ever need to push a file up without waiting for Actions: connect
with an FTP client (FileZilla, Cyberduck) using the same FTP account from
step 1, and upload the contents of `public/wp-content/mu-plugins/` and
`public/wp-content/themes/minimal-reader/` over their matching folders.
Slower and easier to get wrong (easy to miss a deleted file, since FTP
clients don't mirror-delete by default) but works with nothing but the FTP
credentials.

## SSH/rsync method (reference only — not usable on this project's host)

Kept here for a host that actually exposes SSH externally — this project's
current hosting doesn't (see above), so none of this applies until/unless
that changes.

### Manual, from your own machine

```bash
rsync -avz --delete \
  public/wp-content/mu-plugins/ \
  youruser@yourserver:~/public_html/wp-content/mu-plugins/

rsync -avz --delete \
  public/wp-content/themes/minimal-reader/ \
  youruser@yourserver:~/public_html/wp-content/themes/minimal-reader/
```

`--delete` is safe here specifically because each `rsync` targets exactly
one of the two mirrored subdirectories — a file removed from
`mu-plugins/custom-x/` in git correctly disappears on the server too,
without going anywhere near `uploads/` or anything else in `wp-content/`.
**Never point a `--delete`-flagged sync at anything broader** than those
two folders specifically — pointed at `wp-content/` itself, `--delete`
would erase `uploads/` along with everything else that isn't in git.

### Automating it with GitHub Actions

```yaml
name: Deploy to production

on:
  push:
    branches: [main]

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

Secrets this method needs instead: `DEPLOY_SSH_KEY` (the **private** half
of a dedicated deploy keypair — generate with `ssh-keygen -t ed25519 -C
"github-actions-deploy"`, never reuse a personal key), `DEPLOY_HOST`,
`DEPLOY_USER` (the SSH/hosting account username), and
`DEPLOY_MU_PLUGINS_PATH`/`DEPLOY_THEME_PATH` as **absolute** filesystem
paths this time (e.g. from `cd ~/public_html/wp-content/mu-plugins && pwd`
over SSH) — unlike the FTPS method's paths, which are relative to the FTP
account's own root.

The public half of that keypair would need to go into the server's own
`~/.ssh/authorized_keys` (append, don't overwrite, if other keys are
already trusted there) — on a host where SSH is actually reachable from
outside, that's the correct place for it, not a panel's own "Import SSH
Key" UI unless that UI specifically documents itself as managing
`authorized_keys` (1Panel's doesn't — see above).

## After any sync

If a plugin added new functionality that needs activation-time setup
(a new option, a rewrite rule), that's the same one-time step on production
as it was locally — check that plugin's `docs/plugins/*.md`. Routine code
updates need nothing further; MU-plugins load automatically and there's no
"activate" step.
