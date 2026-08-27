# custom-post-tabs

**Location:** `public/wp-content/mu-plugins/custom-post-tabs.php` (loader) + `public/wp-content/mu-plugins/custom-post-tabs/` (implementation)
**Type:** MU-plugin (always active) that registers a Gutenberg block

## What it is

`minimal-reader/post-tabs` — search "Post Tabs" in the block inserter. A
tabbed posts list, currently with two tabs:

- **Latest** — newest posts first (`orderby: date`).
- **Discussions** — posts with the most comments first
  (`orderby: comment_count`, falling back to date on a tie).

Switching tabs is instant (no page reload) — both tabs' posts are rendered
server-side up front, and a small script just toggles which one is visible.

There's no "Top" (by view count) tab yet. WordPress has no native concept
of post popularity/views — unlike comment count, which core already
tracks — so adding it means building a real view-counter first (with its
own caching/spam-refresh considerations), which is a separate decision, not
bundled into this block.

## Configuring

The only setting, in the block's sidebar, is **Posts per tab** (1–10,
default 5) — applies to every tab equally.

## Featured image

Each post's row shows its Featured Image if it has one, cropped to a
dedicated `mlpt-thumb` size (320×200, hard-cropped) so every row's
thumbnail is a consistent shape — same reasoning and the same "regenerate
thumbnails for pre-existing images" caveat as
[`custom-featured-carousel.md`](./custom-featured-carousel.md#featured-image).

## How it works

- `block/render.php` — runs two `WP_Query`s (both excluding translation
  posts via `mlp_exclude_translations_meta_query()` if
  `custom-multilingual-post` is active, same as the carousel), renders a
  `role="tablist"` nav plus one `role="tabpanel"` per tab. Only the first
  panel starts visible (`hidden` attribute on the rest) — pure progressive
  enhancement, since the HTML is complete and valid even if `tabs.js`
  somehow fails to load, it would just show the "Latest" panel with no way
  to switch.
- `block/editor.js` — sidebar UI: just a `RangeControl` for posts-per-tab.
  The canvas preview doesn't try to render real post data (same approach as
  the carousel) — a plain note that the real tabs render on the frontend.
- `assets/tabs.js` — vanilla JS: on click, toggles `.is-active` and
  `aria-selected` on the tab buttons, and the `hidden` attribute +
  `.is-active` on the matching panel. Only enqueued on pages that actually
  use the block (`has_block()`).
- `custom-post-tabs.php` registers the `mlpt-thumb` image size on
  `after_setup_theme`, same pattern as `custom-featured-carousel.php`.

Visual styling (`.post-tabs`, `.post-tabs__tab`, `.post-tabs__item`, …)
lives in the active theme's stylesheet, same split as every other custom
plugin in this project.

## Files

```
custom-post-tabs.php              top-level MU loader (required by WP itself)
custom-post-tabs/
  custom-post-tabs.php            internal loader — registers the mlpt-thumb image size, the block, and the frontend JS (only where needed)
  block/
    block.json                    block registration (name, attributes: postsPerTab, editorScript, render)
    editor.js                     sidebar UI: posts-per-tab range control
    render.php                    frontend markup (dynamic block, no saved HTML) — builds both WP_Query tabs
  assets/
    tabs.js                       click-to-switch-tab behavior
```
