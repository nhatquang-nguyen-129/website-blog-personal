# custom-table-of-contents

**Location:** `public/wp-content/mu-plugins/custom-table-of-contents.php` (loader) + `public/wp-content/mu-plugins/custom-table-of-contents/` (implementation)
**Type:** MU-plugin (always active) that registers a Gutenberg block

## Problem statement

WordPress core has no block that scans a post's own headings and builds a
linked table of contents from them — the closest core gets is the List
block, which requires manually retyping every entry and re-editing it by
hand whenever a heading changes. The old Payload CMS this project migrated
from had this built in, and it wasn't carried over, so it was a genuine
missing capability rather than a stylistic preference.

## What it is

A **Table of Contents** block for the block editor's inserter — search "Table
of Contents" and drop it wherever you want it in a post (usually right after
the intro paragraph). It replaces the auto-generated ToC block that existed
in the old Payload CMS, which wasn't carried over during migration.

There is nothing to configure beyond an optional title (defaults to "Mục
lục", editable directly in the block). The list of links is **not** stored —
it's rebuilt every time the post is viewed, from whatever `<h1>`–`<h4>` tags
are actually in the post at that moment. Edit a heading's text or add a new
one, and the ToC updates on its own; there's no "sync" step.

No build tooling (no Node/webpack) is involved — the block's editor UI is
plain JavaScript using `wp.blocks`/`wp.element`/`wp.blockEditor` directly,
and it's registered via a `block.json` `"render"` field (WordPress 6.1+)
pointing at a PHP file, so the frontend markup is plain server-rendered PHP.

## How the anchors work

1. `core/headings.php` — `mlptoc_extract_headings( $content )` scans raw post
   HTML with a regex for `<h1>`–`<h4>` tags, strips inner markup down to
   plain text, and slugifies each heading (`sanitize_title()`) into an id.
   Duplicate headings get `-2`, `-3`, … appended so ids stay unique.
2. `block/render.php` calls that helper against the post's raw
   `post_content` to build the `<nav class="mlptoc">` list of `<a
   href="#slug">` links — this is the block's actual frontend output
   (`block.json`'s `"render"` target).
3. A `the_content` filter (priority 20, so it runs after blocks/`wpautop`
   have produced the final HTML) calls the *same* helper against the
   rendered content and injects `id="slug"` into each real heading tag that
   doesn't already have one.

Both call sites walk the same headings in the same document order, so the
ids line up without the two ever passing data to each other directly.

If a post has fewer than 2 headings, the block renders nothing (a table of
contents for 0–1 items isn't useful).

## Files

```
custom-table-of-contents.php             top-level MU loader (required by WP itself)
custom-table-of-contents/
  custom-table-of-contents.php           internal loader — registers the block via block.json
  core/
    headings.php                        heading extraction/slugging + the id-injection the_content filter
  block/
    block.json                          block registration (name, attributes, editorScript, render)
    editor.js                           block editor UI (vanilla JS, no build step) — just an editable title
    render.php                          frontend markup (dynamic block, no saved HTML)
```

Visual styling (`.mlptoc`, `.mlptoc__title`, `.mlptoc__list`, …) lives in the
active theme's stylesheet, not in this plugin — same approach as
`custom-multilingual-post`'s `.mlp-language-switcher` (see
[custom-multilingual-post.md](./custom-multilingual-post.md)), so the plugin
stays theme-agnostic. `minimal-reader` styles it as a bordered box with an
uppercase accent-colored label, matching the language switcher and post-hero
category label.
