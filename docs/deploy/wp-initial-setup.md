# WordPress initial setup (one-time)

Do this once, when the domain first goes live. After this, `github-workflows.md`
covers every future update.

**Before any of this**: confirm the domain is actually *bound* to the
website/document root you're about to install into, not just pointed at
the server's IP via DNS — those are two different steps. On 1Panel/iNET
specifically, that means adding the domain as an **Alias Domain**, not an
**Addon Domain** — see `inet-onepanel-setup.md`'s warning on this. Getting
this wrong looks like a WordPress or security problem (`install.php`
returning `403`, other real files 404ing) when the actual cause is that
the domain was never routed to the right folder at all.

## 1. Create the database

cPanel → **MySQL Databases** (on 1Panel/iNET: Website Management →
**MySQL Manager** — see `inet-onepanel-setup.md`):

1. Create a database (e.g. `youruser_blog`  — cPanel usually prefixes it
   with your account name).
2. Create a database user with a strong, generated password.
3. Add that user to that database with **All Privileges**.

Write down: DB name, DB user, DB password, DB host (almost always
`localhost` on shared hosting).

## 2. Get WordPress core onto the server

Locally, the `Dockerfile` always grabs whatever's currently "latest" at
build time, because a container is disposable and gets rebuilt from
scratch regularly. A **live site is not disposable** the same way — install
a specific current version now, and let WordPress's own admin-dashboard
updater handle core upgrades from then on (Dashboard → Updates). That
updater is a completely separate, WordPress-native path from this repo's
deploy process — it only ever touches core files, never `wp-content/`, so
it can never conflict with anything this repo manages.

**If the panel has a one-click WordPress installer** (1Panel/iNET calls it
**WordPress Manager** — see `inet-onepanel-setup.md`; cPanel's equivalent is
usually Softaculous): fine to use it just to get a clean WordPress core
onto the server quickly. Its own generated `wp-config.php` gets thrown away
in step 4 below regardless, so it doesn't matter that the installer manages
that file differently than this project would.

**If you have SSH and no installer, or prefer doing it by hand:**

```bash
cd ~/public_html   # or wherever the domain's document root is
curl -O https://wordpress.org/latest.zip
unzip latest.zip
mv wordpress/* wordpress/.htaccess . 2>/dev/null; rmdir wordpress
rm latest.zip
```

**If you don't have SSH** (FTP/cPanel File Manager only): download
`https://wordpress.org/latest.zip` to your own machine, unzip it locally,
and upload the contents of the `wordpress/` folder into the document root
via FTP or cPanel's File Manager "Upload" — same end result, just through
the browser/FTP client instead of `curl`+`unzip` on the server.

Either way, you now have a stock WordPress install sitting in the document
root — `wp-admin/`, `wp-includes/`, the root PHP files, and a default
`wp-content/` — identical in spirit to what the `Dockerfile` produces
locally before this repo's `wp-content` gets layered on.

## 3. Layer this repo's `wp-content` on top

Only two things from this repo ever go onto the live server:

- `public/wp-content/mu-plugins/`
- `public/wp-content/themes/minimal-reader/`

Nothing else — not `wp-config.php`, not the root PHP files, not
`wp-content/uploads/`. Get the current `branch_1x` (or `main`, once merged)
checkout of those two folders onto the server, merged into the
`wp-content/` that step 2 created (don't delete the default
`wp-content/plugins/` or `wp-content/themes/twentytwenty*` — leaving them
alongside is harmless; `minimal-reader` just needs to end up as the
*active* theme, which happens in step 5).

See `github-workflows.md` for exactly how to get those two folders onto the
server (SSH+git+rsync, or FTP) — the mechanism is the same for this first
deploy as for every deploy after it, so that doc covers it once rather than
repeating it here.

**Sanity-check what actually landed**, especially right after a
`branch_1x` → `main` merge: `ls wp-content/mu-plugins/` and confirm every
top-level `.php` file there is one you recognize (matches a
`docs/plugins/*.md`). A merge that resolves a conflict can silently bring
back a file one branch had already deleted/renamed if the other branch
had also touched it — MU-plugins load every top-level `.php` file in that
directory unconditionally, so an old, superseded copy sitting alongside
its replacement isn't just dead weight, it can mean duplicate function
definitions and a fatal error on every page load. This happened once in
this project's own history (an old `multilingual-post.php` resurrected
alongside `custom-multilingual-post.php` — see `CHANGELOG.md`'s v1.0.1).

## 4. Create `wp-config.php`

Not the same file as `docker/wp-config.docker.php` — that one has
hardcoded placeholder salts and `WP_DEBUG` on, fine for a disposable local
container, wrong for a public site.

If your file manager's "Copy" doesn't let you paste-as-a-new-name in the
same folder, don't edit `wp-config-sample.php` directly to "convert" it in
place — that leaves no clean sample to fall back on if you make a mistake,
and the sample's original content isn't otherwise saved anywhere in this
repo (it ships inside the WordPress core zip, not tracked in git). Use
**New file** → `wp-config.php` instead, and paste the structure below into
it directly:

```php
define('DB_NAME', 'youruser_blog');
define('DB_USER', 'youruser_blog');
define('DB_PASSWORD', 'the real generated password');
define('DB_HOST', 'localhost');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

// Real, unique values — generate at https://api.wordpress.org/secret-key/1.1/salt/
// and paste the whole block it gives you here, replacing these placeholders.
define('AUTH_KEY',         'put a unique phrase here');
define('SECURE_AUTH_KEY',  'put a unique phrase here');
define('LOGGED_IN_KEY',    'put a unique phrase here');
define('NONCE_KEY',        'put a unique phrase here');
define('AUTH_SALT',        'put a unique phrase here');
define('SECURE_AUTH_SALT', 'put a unique phrase here');
define('LOGGED_IN_SALT',   'put a unique phrase here');
define('NONCE_SALT',       'put a unique phrase here');

$table_prefix = 'wp_';

define('WP_DEBUG', false);

// Hardening that also reinforces this project's own rule (CLAUDE.md): all
// real changes go through git, never through the wp-admin file editor or
// an ad-hoc plugin install. These make that the *only* way, not just the
// convention.
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

require_once ABSPATH . 'wp-settings.php';
```

`DISALLOW_FILE_MODS` also disables installing plugins/themes from
wp-admin — intentional here, since this project's whole approach is "if
WordPress doesn't already do it, it's a plugin in this repo," never a
WordPress.org install. If that ever needs to change, remove that one line.

## 5. Run the install wizard

Visit `https://yourdomain.com/wp-admin/install.php`, fill in site
title/admin account — the same screen you already went through locally.

Then, matching the local setup (see `docs/themes/custom-minimal-reader.md` and
`docs/plugins/`):

1. **Appearance → Themes** — activate `Minimal Reader`.
2. **Settings → Permalinks** — pick "Post name" (or your preferred pretty
   structure) and Save, even without changing anything — this is what
   generates the real `.htaccess` rewrite rules; skipping it leaves plain
   `?p=123`-style URLs.
3. **Pages** — create your Home and Blog pages (see
   `docs/themes/custom-minimal-reader.md`'s "blank canvas" section for how the
   homepage is meant to be built from blocks), then **Settings → Reading**
   → "A static page" → pick them.
4. Set **Settings → General** site title/tagline, and **Appearance → Menus**
   for the header nav (see `docs/themes/custom-minimal-reader.md`'s Header
   section — it's empty until a menu is assigned to "Primary Menu").
5. Sanity check: open a page, confirm no PHP errors/warnings appear and
   the theme's fonts/colors show up correctly (confirms `mu-plugins` and
   `minimal-reader` both actually made it onto the server intact).

From here on, updates flow through `github-workflows.md`.
