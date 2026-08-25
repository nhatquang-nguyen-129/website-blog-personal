# multilingual-post

**Location:** `public/wp-content/mu-plugins/multilingual-post.php` (loader) + `public/wp-content/mu-plugins/multilingual-post/` (implementation)
**Type:** MU-plugin (always active, not togglable from the Plugins screen)

## What it is — and isn't

This is **not** an auto-translation plugin. Nothing is machine-translated. Every
language version is written and edited by hand as its own WordPress post/draft.

What it actually does: let one piece of content be read at **one shared public
URL**, in whichever of its manually-written translations the reader picks via
a language toggle on the page. The URL in the browser's address bar never
changes when switching language.

## Data model

- Translations of the same content are linked by a shared `_ml_group` post
  meta value (a random group id).
- Exactly one post per group is flagged `_ml_is_original` (`1`) — this is the
  post that was there first, before any translation existed. Its permalink is
  the group's one canonical public URL.
- Every post in a group (original and translations alike) carries `_ml_lang`
  (e.g. `vi`, `en`) recording which language it's written in.
- Translations are ordinary WordPress posts (own post row, own editor screen,
  own revisions) — they're just never meant to be visited directly once
  published. See "Same URL, not same post" below.

Supported languages are declared in one place: `mlp_available_langs()` in
`core/languages.php`. Add a language by adding an entry there (and writing a
`mlp_available_langs` filter from a theme/feature if you'd rather not edit
the plugin directly).

## Same URL, not same post

Two things make "same URL" actually hold up, in `core/language-resolver.php`:

1. **Content swap on the canonical URL.** When the original post's permalink
   is requested with `?lang=xx`, the plugin hooks `the_post` and — if a
   *published* sibling in that language exists in the group — swaps the
   global `$post` for that sibling before the template renders. The URL
   (path + `?lang=`) is all that changes; everything else about the request
   (permalink, canonical link, browser history entry) stays put.
2. **Canonical redirect for translation permalinks.** A translation post
   still has its own permalink (WordPress always gives every post one), but
   visiting it directly 301-redirects to `original permalink + ?lang=<that
   post's language>`. This keeps exactly one URL per piece of content in the
   eyes of both readers and search engines — translations are an editing
   convenience, not a second indexable page. `wp_head` also prints
   `hreflang` alternate tags for the same reason.

If no `?lang=` is present, or the requested language has no published
sibling yet, the original simply renders as-is.

## Editing workflow

1. Write the post in your primary language, save/publish normally. On first
   save it becomes the group's `_ml_is_original` post (this only happens once
   a translation is actually added — see below — not just from opening the
   editor).
2. In the **Multilingual Versions** box (post-edit sidebar), pick a language
   from the "Add translation…" dropdown and click **Add**. This clones the
   current post (title, content, excerpt, featured image, taxonomy terms)
   into a new **draft** in that language, tagged into the same group, and
   jumps you to its editor.
3. Translate the draft by hand, then publish it. From that point on, readers
   see a language toggle on the canonical URL and can switch to it.

The **Post Language** box (a separate metabox) sets/edits a post's own
`_ml_lang` — used both for the dropdown above and for the frontend toggle
label.

A language can only exist once per group — the "Add translation…" dropdown
only lists languages the group doesn't already have, and the server-side
duplication also refuses to create a second one.

## Frontend: the `[language_switcher]` shortcode

Drop `[language_switcher]` into a post/template. It renders nothing unless
the group has 2+ *published* posts. Otherwise it prints a toggle like:

```html
<div class="mlp-language-switcher">
  <a href="/mafia-review/?lang=vi" class="mlp-active" aria-current="true">Tiếng Việt</a>
  <a href="/mafia-review/?lang=en">English</a>
</div>
```

Every link points at the *same* canonical (original) permalink — only
`?lang=` differs. No JavaScript is required for the toggle to work; style
`.mlp-language-switcher` / `.mlp-active` in the theme as needed.

## Security notes

- The "Add translation" AJAX endpoint (`wp_ajax_mlp_create_translation`)
  requires a per-post nonce and `current_user_can( 'edit_post', $post_id )`.
- The `save_post` handler that writes `_ml_lang` requires the same nonce +
  capability check, and bails out during autosave.
- The admin JS for the "Add" button only loads on the post-edit screens
  (`admin_footer-post.php` / `admin_footer-post-new.php`), not site-wide.
- All frontend/admin output that includes a URL or user-influenced value goes
  through `esc_url()` / `esc_html()` / `esc_attr()`.

## Files

```
multilingual-post.php                    top-level MU loader (required by WP itself)
multilingual-post/
  multilingual-post.php                  internal loader — require order matters (languages → post-group → duplicate-post → language-resolver)
  core/
    languages.php                        supported language list + labels
    post-group.php                       group/original/lang lookups (the data layer)
    duplicate-post.php                   clone a post into a new-language draft
    language-resolver.php                the same-URL mechanism: content swap, canonical redirect, hreflang
  admin/
    metabox-language.php                 "Post Language" metabox + save handler
    metabox-translations.php             "Multilingual Versions" metabox (list + add-translation UI)
    ajax-create-translation.php          AJAX endpoint + its admin JS
  frontend/
    language-switcher.php                [language_switcher] shortcode
```
