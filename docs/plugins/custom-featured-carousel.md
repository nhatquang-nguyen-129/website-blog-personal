# custom-featured-carousel

**Location:** `public/wp-content/mu-plugins/custom-featured-carousel.php` (loader) + `public/wp-content/mu-plugins/custom-featured-carousel/` (implementation)
**Type:** MU-plugin (always active) that registers a Gutenberg block

## Problem statement

WordPress core has no carousel/slider block, and no way to hand-pick and
reorder a specific, curated set of posts for a spotlight — the core Query
Loop block can filter and sort a query, but it can't express "these exact
posts, in this exact order, regardless of any query criteria." Sticky
Posts was considered and deliberately rejected for this (see "Choosing
what to show" below): it's a single global flag, not a per-instance,
reorderable, capped-at-10 pick list, and using it here would mean any page
with this block shares the same picks. Neither gap closes with block
composition alone, which is why this is a plugin rather than a Query Loop
variation.

## What it is

`minimal-reader/featured-carousel` — search "Featured Carousel" in the block
inserter. An auto-advancing, full-width carousel. It's a **feature**, not a
page layout: insert it anywhere, on any page, remove it if you don't want
one, move it wherever you like. (The homepage itself is a blank-canvas Page
template with no hardcoded sections — this block, the core Categories
block, and a core Query Loop are what actually build it, all editable in
the UI.)

> If you put this block on your homepage Page and it isn't showing up at
> `/`, check **Settings → Reading**: "Your homepage displays" needs to be
> **"A static page"** with that Page selected. This is a general WordPress
> Reading Setting, not something specific to this block — see
> [`docs/themes/custom-minimal-reader.md`](../themes/custom-minimal-reader.md#why-a-static-page-matters-settings--reading)
> for why.

There's no fallback data source (e.g. Sticky Posts) — the carousel only
ever shows what you explicitly add, and renders nothing at all until you've
added at least one post.

## Featured image

Each slide uses the post's own **Featured Image** — no separate carousel-only
image to upload. WordPress auto-generates a dedicated `mlfc-carousel` image
size (1600×500, hard-cropped, centered) from that same Featured Image the
moment it's uploaded, so there's nothing to configure per-post.

For the crop to look intentional rather than arbitrarily cut off, upload a
Featured Image that's **at least 1600px wide and reasonably wide/landscape**
(close to a 3:1 ratio) if you want control over what ends up in frame — a
narrow portrait photo will have its sides cropped away to fill that shape.
Posts with a narrower or smaller image still work, just with more of the
frame decided by WordPress's centered crop rather than the photo's own
framing.

If a Featured Image was uploaded before this size existed, regenerate it
(a "Regenerate Thumbnails" plugin, or `wp media regenerate` via WP-CLI) —
otherwise WordPress falls back to scaling the next closest size up, which
looks soft/blurry on a full-width slide.

## Choosing what to show

In the block's sidebar, under **Featured posts**:

1. Paste a post's URL into **Post link** and click **Add**. The link is
   resolved to an actual post over the REST API — it works with a plain
   `?p=123` URL or a pretty `/my-post-slug/` one, whichever this site is
   using.
2. Repeat for each post you want featured — up to **10**.
3. **Drag posts by the ⠿ handle to reorder** them; that order is exactly
   what the carousel shows. Click the × to remove one.

Only *published* posts render — an added post that's later unpublished is
silently skipped rather than erroring, and the list caps at 10 even if more
were somehow added.

## Autoplay

**Slide delay (ms)** in the sidebar (1000–15000, default 5000) sets how long
each slide stays up before advancing — passed straight through to the
carousel's `data-autoplay` attribute. A single-slide carousel never
autoplays (nothing to advance to), regardless of this setting.

Autoplay pauses while a reader is hovering or has keyboard focus inside the
carousel (a link, the arrows, a dot), and while the browser tab itself is in
the background (`document.hidden`) — tracked as two flags rather than
calling stop/start straight off each hover/focus event, since either one
ending shouldn't resume it while the other still holds. Switching browser
tabs never fires `mouseleave`/`focusout` on the now-hidden page, so on
`visibilitychange` back to visible, both flags are reset outright rather
than trusted — otherwise a carousel a reader was hovering right before
switching tabs would stay paused indefinitely after they came back.

## Manual navigation

With 2+ slides, readers can also move between them directly — any manual
move restarts the autoplay clock so the next auto-advance doesn't land a
moment later:

- **Arrows** (‹ ›) on either side of the carousel.
- **Dots** below it, one per slide.
- **Two-finger trackpad swipe** (macOS and similar) — detected as a `wheel`
  event whose `deltaX` dominates `deltaY`.
- **Touch swipe** on phones/tablets (`touchstart`/`touchend`, ~40px
  threshold).

## How it works

- `block/render.php` — the dynamic block's frontend markup. Builds a
  `WP_Query` from the `postIds` attribute (capped to 10, `orderby:
  post__in` to preserve the picked order, `ignore_sticky_posts: true` so
  WordPress doesn't quietly prepend an unrelated Sticky Post ahead of the
  list), renders each as a slide (the `mlfc-carousel`-sized Featured Image
  as a real `<img>` if the post has one, otherwise a plain accent-colored
  card), and stamps the delay onto the wrapper. Returns nothing if
  `postIds` is empty. The image is a real `<img>` rather than a CSS
  `background-image` specifically so it keeps WordPress's automatic
  `srcset`/`sizes` responsive-image markup — a `background-image` never
  gets that, so the browser would otherwise always fetch one fixed
  resolution regardless of viewport size or pixel density.
- `custom-featured-carousel.php` registers the `mlfc-carousel` image size
  (1600×500, hard-cropped) on `after_setup_theme` — see "Featured image"
  above.
- `block/editor.js` — the sidebar UI: a URL field + Add button that
  resolves a pasted link to a post via `/wp/v2/posts/{id}` (numeric `?p=`
  links) or `/wp/v2/posts?slug=…` (pretty-permalink links), a
  native-HTML5-drag-and-drop list for reordering (`draggable`, `onDragStart`/
  `onDragOver`/`onDrop` — no external library), and the delay `RangeControl`.
  The canvas preview lists whatever's currently in `postIds`, or a warning
  if it's empty.
- `block/editor.css` — styling for that sidebar UI specifically. **Not**
  the theme's `add_editor_style()` mechanism: `InspectorControls` render in
  the outer editor chrome, not inside the block-content iframe that
  `add_editor_style()` reaches, so this is enqueued directly via
  `enqueue_block_editor_assets` instead.
- `assets/carousel.js` — the actual autoplay/pause-on-hover/dots behavior
  on the frontend, vanilla JS, shared by whatever markup `render.php`
  outputs. Only enqueued on pages that actually use the block
  (`has_block()`), and respects `prefers-reduced-motion`.
- Like `custom-table-of-contents`, the editor script is registered
  explicitly with `wp-blocks`/`wp-element`/`wp-block-editor`/
  `wp-components`/`wp-api-fetch`/`wp-i18n` as dependencies rather than
  relying on block.json's bare `editorScript` (which only picks up
  dependencies from a build-tool-generated `editor.asset.php` — there's no
  build step here).

Visual styling for the carousel *itself* (`.home-carousel`,
`.home-carousel__slide`, …) lives in the active theme's stylesheet, same
split as every other custom plugin in this project — the plugin owns the
markup/behavior/data, the theme owns how it looks. The sidebar-only CSS
above is the one exception, since that UI is specific to this block and
isn't something a theme would ever want to restyle.

## Files

```
custom-featured-carousel.php              top-level MU loader (required by WP itself)
custom-featured-carousel/
  custom-featured-carousel.php            internal loader — registers the block with explicit script deps, enqueues the editor sidebar CSS and the frontend carousel JS only where needed
  block/
    block.json                            block registration (name, attributes: postIds/delay, editorScript, render)
    editor.js                             sidebar UI: URL-to-post resolver, drag-to-reorder list, slide delay
    editor.css                            styling for that sidebar UI (see note above on why it's separate from the theme's editor.css)
    render.php                            frontend markup (dynamic block, no saved HTML)
  assets/
    carousel.js                           autoplay/pause-on-hover/dots behavior
```
