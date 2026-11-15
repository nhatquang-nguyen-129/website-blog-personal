# custom-multilingual-post

**Location:** `public/wp-content/mu-plugins/custom-multilingual-post.php` (loader) + `public/wp-content/mu-plugins/custom-multilingual-post/` (implementation)
**Type:** MU-plugin (always active, not togglable from the Plugins screen)

## Problem statement

WordPress core has no multilingual capability at all — no linked-translation
data model, no same-URL language switching, no `hreflang` generation.
WordPress.org's multilingual plugins (WPML, Polylang, and similar) solve
this generically for any site, which means heavy footprints and their own
opinions about URL structure (usually a language-prefixed path or
subdomain) that don't fit this project's specific requirement: one shared
canonical URL per piece of content, regardless of which language is being
read. That specific shape was worth a small purpose-built plugin rather
than configuring a general-purpose one against the grain of what it
expects.

## What it is — and isn't

This is **not** an auto-translation plugin. Nothing is machine-translated. Every
language version is written and edited by hand as its own WordPress post/draft.

> **Rule for any change to this plugin:** language versions are fully
> independent posts, each written and edited by hand by its author. No
> feature may copy, sync, merge or overwrite content between them. The
> one-time clone made by **Add translation** is a starting draft, not a
> link. Features here may only affect *addressing* (URLs, redirects, head
> tags), *listing* (what appears where) and *comments*, never a post's own
> content.

What it actually does: let one piece of content be read at the original's
URL, in whichever of its manually-written translations the reader picks via
a language toggle on the page. The path never changes when switching
language; only `?lang=` does.

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
- **If the original stops being published** (unpublished, trashed or
  deleted), the `_ml_is_original` flag is handed to the next published post
  in the group (`mlp_hand_over_original()`). Public lists show only
  originals, so otherwise every remaining translation would vanish from
  them, reachable only by direct link. Only the flag moves, never content.
  The group's URL becomes the new original's permalink, and a demoted
  original that's later republished stays a translation of it.

Supported languages are declared in one place: `mlp_available_langs()` in
`core/languages.php`. Add a language by adding an entry there (and writing a
`mlp_available_langs` filter from a theme/feature if you'd rather not edit
the plugin directly).

## Same URL, not same post

Two things make "same URL" actually hold up, in `core/language-resolver.php`:

1. **Content swap on the original's URL.** When the original post's
   permalink is requested with `?lang=xx`, the plugin hooks `the_post` and
   — if a *published* sibling in that language exists in the group — swaps
   the global `$post` for that sibling before the template renders. The
   path stays the same; only `?lang=` differs.
2. **Redirect for translation permalinks.** A translation post still has
   its own permalink (WordPress always gives every post one), but visiting
   it directly 301-redirects to `original permalink + ?lang=<that post's
   language>`. Translations are an editing convenience, not a second
   address for the same language.

If no `?lang=` is present, `?lang=` names the original's own language, or
the requested language has no published sibling yet, the original simply
renders as-is.

### One URL per language

Every language has exactly one URL, built by `mlp_lang_url()`:

| Language | URL |
|---|---|
| The original's own language (e.g. `vi`) | the plain permalink, `/mafia-review/` |
| Any other published language (e.g. `en`) | `/mafia-review/?lang=en` |

`?lang=vi` on a `vi` original renders the same page, but declares the
plain URL as its canonical, so it's never treated as a separate page.

### `<head>` tags follow the language being shown

`mlp_requested_translation()` resolves once per request which translation,
if any, is being shown, and every tag below reads from it, so they can't
disagree:

- **`rel="canonical"`** (`get_canonical_url` filter): `?lang=en` declares
  itself canonical. Before this, every language declared the original's
  plain URL as canonical. That contradicted the `hreflang` tags, which
  list `?lang=en` as its own version, and search engines treat a page
  canonicalized elsewhere as a duplicate and leave it out of the index.
- **`hreflang`** alternates: one per published language at its
  `mlp_lang_url()`, plus `x-default` at the plain URL. Only printed when
  the group has 2+ published languages.
- **`<title>`** (`document_title_parts` filter): the shown translation's
  title. Core builds it from the queried object (the original), which the
  content swap doesn't change.
- **`<html lang>`** (`language_attributes` filter): the shown post's
  `_ml_lang`, on any post that's part of a group, instead of always the
  site's language setting.

### Links and sitemap

On the frontend, a published translation's permalink *is* its language's
URL: `post_link`/`page_link` return `mlp_lang_url()` instead of the
translation's own slug, which would only 301 there. Any link WordPress
builds to a translation (search results, feeds, a link pasted in a post)
goes straight to the right place. Core's `/wp-sitemap.xml` lists posts by
`get_permalink()`, so it lists each published translation at its
`?lang=xx` URL, alongside the original at its plain URL, with no custom
sitemap code. In wp-admin, in REST requests (the block editor builds its
View/Preview links from the REST response), and for unpublished
translations, the post keeps its own permalink, so editing, slug changes
and previews work as usual. The redirect from a translation's own
permalink is likewise skipped for previews (`is_preview()`), so previewing
a draft translation, or unsaved changes to a published one, shows that
translation rather than the original.

## Where translations appear

- **Lists** (home, blog, archives, feeds) show only originals:
  `pre_get_posts` adds `mlp_exclude_translations_meta_query()` to the main
  query. Custom queries (Featured Carousel, Post Tabs) merge in the same
  helper, and the core **Query Loop** block gets it via
  `query_loop_block_query_vars`, since it runs its own query that
  `pre_get_posts`' main-query check doesn't cover.
- **Search** is the exception: it searches every language, so a post can
  be found by words from any of its versions. `mlp_dedupe_search_results()`
  then keeps one result per group. That's the original if it matched, so
  a term shared by every language (a game's name) doesn't send a
  Vietnamese reader to the English version; otherwise it's the first
  matching translation, linked to its `?lang=xx` URL.

## Comments: one discussion per post

Each language is its own post, so out of the box each one had its own
comment thread, and Post Tabs' "Discussions" tab (ranked by the original's
`comment_count`) never counted comments left on a translation.
`frontend/shared-comments.php` makes it one discussion per piece of
content, on any post in a group:

- New comments are always filed on the original (`comment_id_fields`),
  whichever language's form they come from.
- The thread shows comments from every published post in the group
  (`comments_template_query_args` → `post__in`), so comments left on a
  translation before this keep showing.
- The count (`get_comments_number`) is the group's total; open/closed
  (`comments_open`) follows the original.
- After posting from a translation, the reader is redirected back to that
  language's URL (a hidden `mlp_lang` field + `comment_post_redirect`),
  keeping core's `unapproved`/`moderation-hash` args so they still see
  their comment awaiting moderation.

This only touches comments; each post's own content is untouched.

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

## Frontend: picking a language

`mlp_render_language_picker()`, called from the theme's `single.php` above
the title, renders on a post whose group has 2+ *published* languages
(`mlp_language_picker_context()`) and nothing otherwise:

- A pill reading **"Available in other languages"**,
  always in English, the most widely read language, so a reader who can't
  read the post's own language can still find their way.
- Clicking it opens a dropdown of the post's languages, **each named in
  itself** ("Tiếng Việt", "English", "Deutsch", from
  `mlp_available_langs()`, whose labels must be written that way), each
  linking to its `mlp_lang_url()`, the shown one marked `is-active` /
  `aria-current`. Open/close (click, outside click, <kbd>Escape</kbd>) is
  `assets/language-picker.js`, enqueued only where the picker renders.

Plain links, never a redirect: every language stays at its own URL, for
readers and search engines alike. Markup only, with this plugin's own
class names; the theme styles it (orange `--primary` text).

### The `[language_switcher]` shortcode

The theme no longer uses this (the language picker above replaced it), but it's still
available to drop into a post or template. Drop `[language_switcher]` into a post/template. It renders nothing unless
the group has 2+ *published* posts. Otherwise it prints a toggle like:

```html
<div class="mlp-language-switcher">
  <a href="/mafia-review/" class="mlp-active" aria-current="true">Tiếng Việt</a>
  <a href="/mafia-review/?lang=en">English</a>
</div>
```

Every link points at the original's permalink, at each language's
`mlp_lang_url()`: plain for the original's own language, `?lang=xx` for
the others. No JavaScript is required for the toggle to work; style
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
    post-group.php                       group/original/lang lookups (the data layer), handing the original flag over when an original is unpublished
    duplicate-post.php                   clone a post into a new-language draft
    language-resolver.php                the same-URL mechanism: one URL per language (mlp_lang_url), content swap, translation-permalink redirect + links, canonical/hreflang/<title>/<html lang> per language, list/Query Loop exclusion, search de-duplication
  admin/
    rest-api.php                         registers _ml_lang as REST-visible post meta + the two /mlp/v1/ REST routes
    editor-panel.php                     enqueues the sidebar panel's JS/CSS on post-edit screens
    editor-panel.js                      the "Multilingual" sidebar panel (PluginDocumentSettingPanel, vanilla JS)
    editor-panel.css                     small styling for the translation list inside the panel
  assets/
    language-picker.js                   opens/closes the language picker's dropdown
  frontend/
    language-switcher.php                [language_switcher] shortcode (not used by the theme any more)
    language-picker.php                  "Available in other languages" picker markup, enqueues assets/language-picker.js where it renders
    shared-comments.php                  one comment thread per group: file on the original, show the whole group's comments, redirect back to the reader's language
```
