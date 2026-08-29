# custom-newsletter-signup

**Location:** `public/wp-content/mu-plugins/custom-newsletter-signup.php` (loader) + `public/wp-content/mu-plugins/custom-newsletter-signup/` (implementation)
**Type:** MU-plugin (always active) that adds a button to the theme's header

## Problem statement

WordPress core has no concept of a mailing list or subscriber at all — no
table, no REST route, no admin UI for it. Checked against `CLAUDE.md`'s own
core-first checklist before writing any of this: there's nothing native to
configure and nothing to duplicate, so this is a genuinely new capability,
not a rebuild of something core already offers. Scope was deliberately
kept to collection only (see below) rather than reaching for a
WordPress.org newsletter plugin, most of which bundle a full sending
pipeline (SMTP setup, templates, campaign scheduling) this project doesn't
need yet and that would need overriding to match this site's own styling
anyway.

## What it is

A **Subscribe button that opens a popup** to collect an email — not an
always-open inline form. The theme's `header.php` shows this button on
**every page**, in the header's action row right after Search (see
`docs/themes/custom-minimal-reader.md`) — it's there just because this
MU-plugin is active, no separate setup step. There's no Gutenberg block
for this; an earlier version had one (for dropping a second copy into a
page's own content), but since the header button already covers every
page and a block could never actually render *inside* `header.php`
itself, the block was more confusing than useful and was removed.

Clicking the button opens a centered modal: heading, email field + button,
and a consent checkbox that gates the button — it starts disabled and only
becomes clickable once the checkbox is ticked.

This **only collects email addresses into this site's own database**. It
does not send anything — no welcome email, no newsletter delivery, no
Mailchimp/ConvertKit-style integration. That's a deliberate, separate
decision for later: actually sending mail reliably needs a real transport
(shared hosting's default `wp_mail()` is frequently unreliable/flagged as
spam), which is a different problem from collecting the address in the
first place.

## Configuring

The button always uses the defaults built into `core/render.php`'s
`mlns_render_signup()` — heading, button label, and consent text. There's
no settings screen for these; changing them means editing that file
directly, matching how the old RSS link this button replaced was static
too.

## Viewing collected subscribers

**Settings → Newsletter Subscribers** in wp-admin: a plain table (email +
subscribed date) and an **Export CSV** button. Without this page the
collected addresses would just sit in the database with no way to actually
use them — this is the minimum needed for "collect the lead" to mean
anything.

## How it works

- `core/render.php` — `mlns_render_signup()` builds the trigger button +
  modal markup; `header.php` calls it with no arguments, wrapped in
  `function_exists('mlns_render_signup')` so the theme never fatals if
  this plugin were ever missing.
- `core/db.php` — defines `wp_mlns_subscribers` (email, created_at) and
  creates it via a version-checked `dbDelta()` call on `init`. MU-plugins
  never fire `register_activation_hook()` (they're not "activated" the way
  a normal plugin is, they just always load), so the usual
  "create the table on activation" pattern doesn't apply — checking a
  stored `mlns_db_version` option on every request is the standard
  workaround, cheap after the first load since it only actually runs
  `dbDelta()` on a version mismatch.
- `custom-newsletter-signup.php` registers a public, read-and-write
  `POST /mlns/v1/subscribe` REST route. Guards, in order: a honeypot field
  (invisible to a real visitor via CSS, not `hidden`, since some bots skip
  fields a page marks non-displayed — a bot that fills every field fills
  this one too) returns a **fake success** without inserting anything, so a
  bot doesn't learn the field is being checked; then a required `consent`
  boolean, checked server-side too (a disabled Submit button alone doesn't
  stop a direct API request); then `is_email()` validation. A duplicate
  email returns `already_subscribed` rather than an error — that still
  reads as a success to the visitor (see `assets/signup.js`), it just
  doesn't insert a second row.
- `admin/subscribers-page.php` — the Settings page above, plus a CSV export
  handled through `admin_post_mlns_export_csv` (nonce-checked,
  `manage_options`-gated).
- The Submit button starts `disabled` directly in the HTML `core/render.php`
  outputs (works even if `signup.js` fails to load — a visitor just can't
  submit at all, rather than submitting without having agreed to
  anything).
- `assets/signup.js` — plain `fetch()` (not `wp.apiFetch`, to avoid
  pulling in its `wp-i18n`/`wp-hooks` dependencies for one public POST on
  the frontend) against the REST route, whose URL is passed via
  `wp_localize_script` (`mlnsSettings.restUrl`, built from `rest_url()`
  so it adapts to pretty or plain permalinks automatically). On init, the
  modal is moved to be a direct child of `<body>`
  (`document.body.appendChild(modal)`) before anything else — a `position:
  fixed` element centers itself against the viewport only if no ancestor
  sets `transform`/`filter`/`backdrop-filter`/etc., and the header's own
  sticky bar uses `backdrop-filter` for its frosted-glass effect, which
  would otherwise make it the modal's containing block and center the
  popup against the thin header bar instead of the actual screen. Handles
  opening/closing the modal (click the trigger, the backdrop, the ×
  button, or press <kbd>Escape</kbd>; focus moves to the email field on
  open and back to the trigger on close), toggles the Submit button on
  the consent checkbox's `change` event, and shows an inline
  success/error message inside the modal after submitting instead of
  navigating anywhere.

## Files

```
custom-newsletter-signup.php              top-level MU loader (required by WP itself)
custom-newsletter-signup/
  custom-newsletter-signup.php            internal loader — registers the REST route and the frontend JS
  core/
    db.php                                table creation + insert/list/count helpers
    render.php                            mlns_render_signup() — the trigger button + modal markup, called by header.php
  admin/
    subscribers-page.php                  Settings page listing subscribers + CSV export
  assets/
    signup.js                             opens/closes the modal, consent-gates the Submit button, posts to the REST route
```
