# 1Panel (OnePanel) notes

This project's actual hosting (iNET, WP-H1) runs on **1Panel** — branded
"OnePanel" in iNET's own UI — not cPanel. The rest of `docs/deploy/` was
written assuming cPanel's terminology since it's the most common panel in
general; this file maps those same steps onto 1Panel's actual menus and
records what's been confirmed about this specific kind of plan, so a future
deploy doesn't have to re-discover it from scratch.

> Never put this account's real SSH username, server hostname/IP, or
> passwords into a git-tracked file — same rule as `wp-config.php` and
> `docker/.env` already being kept out of git. Keep those in a password
> manager or a local, gitignored note instead. Everything below uses
> placeholders on purpose.

## Navigation map (cPanel term → 1Panel location)

| Task | 1Panel location |
|---|---|
| Create/manage MySQL databases | Website Management → MySQL Manager |
| Add/point a domain | Website Management → Domain |
| Upload/browse files | Website Management → File manager |
| SSL certificates | Website Management → SSL Certificates |
| FTP accounts | Website Management → FTP Account |
| PHP version/extensions | Website Management → PHP Selector |
| One-click WordPress install | WordPress Manager (its own top-level sidebar item) |
| SSH keys | Advanced Features → SSH Manager — **key-based auth only** (Generate SSH Key / Import SSH Key); there's no password SSH login to fall back on |
| Web-based terminal | Advanced Features → Terminal — usable for a quick check without a local SSH client |
| Scheduled tasks | Advanced Features → Cron Jobs |
| Backups | Advanced Features → Backup & Restore |
| Git | Advanced Features → Git Manager — **don't use this for this repo**, see below |

## ⚠️ Don't use 1Panel's own Git Manager for this repo

Advanced Features → Git Manager clones/initializes a git repo **directly
into a website's own document root** (its row shows the site's live path,
e.g. `/public_html`, as the fixed "Repository" target). That's built for
apps where the whole repo *is* the site — the opposite of how this repo is
shaped: only `public/wp-content/mu-plugins/` and
`public/wp-content/themes/minimal-reader/` are tracked, and the live
document root needs to hold a real WordPress core (never in git) with just
this repo's `wp-content` layered on top.

Cloning this GitHub repo directly into the document root via that tool
would dump the repo's own root files (`README.md`, `Dockerfile`, `docs/`,
`.gitignore`, …) onto the live site instead of a working WordPress install.
**Don't point it at this repo.**

Use plain `git` over SSH into a separate directory instead — confirmed
available on this plan (`git version 2.43.7` via Advanced Features →
Terminal) — then rsync just the two tracked folders into the live
`wp-content/...`. That's `github-workflows.md`'s Path A, not repeated here
since the commands are identical regardless of which panel is involved —
this section exists only to flag that 1Panel's *own* Git feature is the
wrong tool for it.

## ⚠️ Adding your real domain: "Alias Domain", not "Addon Domain"

This plan comes with a website already created against a temporary
placeholder hostname (something like
`xxxxxxxx.onehost-wphnxxxxxx.000web.xyz`), document root `~/public_html` —
that's where `wp-initial-setup.md`'s steps actually happen, since it's the
only website that exists until you add one. Pointing your real domain's
DNS at the server's IP (`dns-configuration.md` / `inet-dns-setup.md`) gets
requests to the right *server* — it does **not**, by itself, make 1Panel
serve that domain from `~/public_html`. Website Management → Domain still
only lists the placeholder hostname until the real domain is added there
explicitly.

**Website Management → Domain → Add Domain** offers two tabs, and picking
the wrong one is an easy mistake:

- **Addon Domain** — creates a **new** website with its own, empty
  document root (defaults to `/domains/yourdomain.com`). Using this for
  your real domain leaves it pointing at an empty folder, while everything
  actually installed sits — unreachable — at `~/public_html` under the
  placeholder hostname.
- **Alias Domain** — the correct one. Point it at the *existing* website
  (the placeholder one); the folder-path field auto-fills to that same
  `~/public_html`. No new document root, no second WordPress/database
  prompt — the info banner on that tab says as much.

**Symptom if you pick "Addon Domain" (or never add the domain at all)**:
real files that definitely exist in `~/public_html` (`wp-login.php`,
`readme.html`, …) 404 on the real domain, while `wp-admin/install.php`
specifically comes back `403 Forbidden` from a bare LiteSpeed error page
rather than a WordPress one — a strong sign the request is being handled
by something other than your actual site (an unrouted/default vhost),
not by WordPress or a WAF. Checking WAF status, file permissions, and
`.htaccess` first is a reasonable instinct (all three were, in fact, fine)
but the domain-binding step is worth confirming *before* chasing any of
that — it's the more likely cause on a fresh domain.

## Confirmed for the WP-H1 tier specifically

- SSH: available, key-based only — add a dedicated deploy key's public half
  via SSH Manager → Import SSH Key (generate the pair with `ssh-keygen`
  locally or in CI, same as `github-workflows.md` already describes).
- A web terminal is built into the panel — handy for one-off checks
  (`git --version`, `php -v`, etc.) without needing a local SSH client.
- `git` comes preinstalled on the server itself.
- Resource limits on WP-H1 (from iNET's own listed spec, not a secret):
  5 GB SSD, 2 CPU cores, 2 GB RAM, up to 3 MySQL databases.
- The document root follows the ordinary cPanel-style convention —
  `~/public_html` — confirmed via File manager, no host-specific quirk to
  account for. `wp-initial-setup.md`'s example commands (`cd
  ~/public_html`) work as written on this plan, nothing to substitute.
- MySQL Manager's "Add new database" dialog auto-prefixes both the
  database name and the username with the account's own name — you only
  type the suffix (e.g. `wp`). Confirmed the "User Privileges" section
  defaults to all 18 checked, which already satisfies
  `wp-initial-setup.md`'s "All Privileges" instruction with no extra
  clicking. Use the password-generator button (🔑 icon) rather than typing
  one by hand.
