# custom-multilingual-post

**Location:** `public/wp-content/mu-plugins/custom-multilingual-post.php` (loader) + `public/wp-content/mu-plugins/custom-multilingual-post/` (implementation)
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

One **Multilingual** panel in the post-edit sidebar (a real Gutenberg
`PluginDocumentSettingPanel`, built with the same components — `SelectControl`,
`Button`, `Notice` — WordPress's own panels use, not a classic metabox) covers
the whole workflow:

1. Write the post in your primary language, save/publish normally. On first
   save it becomes the group's `_ml_is_original` post (this only happens once
   a translation is actually added, not just from opening the editor).
2. In the panel, the **Language** dropdown sets the post's own `_ml_lang`
   (saved as ordinary post meta — part of the normal Save/Update, no separate
   button). Below it, every post already in the group is listed with its
   status; pick a language from **Add translation** and click **Add** to
   clone the current post (title, content, excerpt, featured image, taxonomy
   terms) into a new **draft** in that language, tagged into the same group,
   and jump straight to its editor.
3. Translate the draft by hand, then publish it. From that point on, readers
   see a language toggle on the canonical URL and can switch to it.

A language can only exist once per group — **Add translation** only lists
languages the group doesn't already have, and the server-side duplication
also refuses to create a second one.

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

## Editor panel internals

The sidebar UI is a REST-backed Gutenberg plugin, not a classic metabox:

- `_ml_lang` is a properly `register_post_meta()`-registered field
  (`show_in_rest => true`), so the **Language** dropdown just reads/writes it
  through `wp.data`/`@wordpress/core-data` (`useEntityProp`) like any other
  post field — it rides along with the editor's normal Save/Update, no custom
  save handler needed.
- Two custom REST routes back the rest of the panel: `GET /mlp/v1/groups/{id}`
  (the group's posts + available languages, for the list and the "Add
  translation" dropdown) and `POST /mlp/v1/translations` (`{post_id, lang}`,
  calls `mlp_duplicate_post()` and returns the new draft's edit URL). Both
  gate on `current_user_can( 'edit_post', $post_id )`; `wp.apiFetch`'s
  automatic nonce handling covers the security layer classic-metabox code
  would otherwise hand-roll with `wp_nonce_field()`/`check_ajax_referer()`.
- The panel's JS (`admin/editor-panel.js`) is plain `wp.element.createElement`
  calls against `@wordpress/components` — no JSX, no build step, same as the
  Table of Contents block's editor UI.

## Security notes

- Both REST routes require `current_user_can( 'edit_post', $post_id )`.
- `_ml_lang`'s `register_post_meta()` call has an `auth_callback` requiring
  the same capability before it can be written via the REST API.
- All frontend output that includes a URL or user-influenced value goes
  through `esc_url()` / `esc_html()` / `esc_attr()`.

## Files

```
custom-multilingual-post.php              top-level MU loader (required by WP itself)
custom-multilingual-post/
  custom-multilingual-post.php            internal loader — require order matters (languages → post-group → duplicate-post → language-resolver)
  core/
    languages.php                        supported language list + labels
    post-group.php                       group/original/lang lookups (the data layer)
    duplicate-post.php                   clone a post into a new-language draft
    language-resolver.php                the same-URL mechanism: content swap, canonical redirect, hreflang, list-view de-duplication
  admin/
    rest-api.php                         registers _ml_lang as REST-visible post meta + the two /mlp/v1/ REST routes
    editor-panel.php                     enqueues the sidebar panel's JS/CSS on post-edit screens
    editor-panel.js                      the "Multilingual" sidebar panel (PluginDocumentSettingPanel, vanilla JS)
    editor-panel.css                     small styling for the translation list inside the panel
  frontend/
    language-switcher.php                [language_switcher] shortcode
```
