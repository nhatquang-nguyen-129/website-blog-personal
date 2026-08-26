# custom-maintenance-mode

**Location:** `public/wp-content/mu-plugins/custom-maintenance-mode.php` (loader) + `public/wp-content/mu-plugins/custom-maintenance-mode/` (implementation)
**Type:** MU-plugin (always active, not togglable from the Plugins screen)

## What it is

A site-wide maintenance toggle, matching how the old Payload CMS's
`maintenance` global worked: one on/off switch plus a custom title and
message, replacing the entire page for every visitor **except a logged-in
administrator** — so you can still browse/edit the live site while it shows
"back soon" to everyone else.

No plugin from the WordPress.org repo — those are typically page-builder-heavy
and bring their own styling that would need overriding to match this site.
This is a small, self-contained toggle instead.

## Usage

**Settings → Maintenance Mode** in wp-admin: a checkbox ("Enable maintenance
mode"), a title field, and a message field. That's the whole interface — no
Customizer panel, no separate CPT.

## How it works

- `core/options.php` — `mlmm_get_options()` / `mlmm_is_enabled()` read one
  option, `mlmm_options` (an array: `enabled`, `title`, `message`), with
  sane defaults if it's never been saved.
- `admin/settings-page.php` — a classic WordPress Settings API page (nothing
  React-based here; a single on/off toggle doesn't need it). Registered
  under **Settings**, capability-gated to `manage_options`.
- `frontend/maintenance-page.php` — hooks `template_redirect`. If maintenance
  mode is on and the current visitor can't `manage_options`, it renders a
  **self-contained** HTML page (own inline `<style>`, not the active theme's
  stylesheet — it needs to render reasonably no matter what theme is active)
  and calls `exit`, sending a `503` status + `Retry-After` header (so search
  engines don't treat the outage as permanent). Logged-in administrators see
  the site completely normally; `wp-admin`, `wp-login.php`, REST, and
  admin-ajax are all untouched since `template_redirect` never fires for
  those, so you're never at risk of locking yourself out.

## Files

```
custom-maintenance-mode.php              top-level MU loader (required by WP itself)
custom-maintenance-mode/
  custom-maintenance-mode.php            internal loader
  core/
    options.php                          reads the mlmm_options option (with defaults)
  admin/
    settings-page.php                    Settings → Maintenance Mode page
  frontend/
    maintenance-page.php                 template_redirect hook + the self-styled maintenance page
```
