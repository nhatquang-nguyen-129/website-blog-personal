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

Each tab is paginated at **Posts per tab** posts per page (see Configuring
below), with numbered pagination — "1 2 3 … 100" style, collapsing to `…`
past a small window around the current page. Unlike the tab switch itself,
pagination *can't* pre-render every page up front (that would mean loading
every post on the site into the page just to view "Latest"), so clicking a
page number fetches that page from a REST endpoint
(`/mlpt/v1/posts?tab=…&page=…&per_page=…`) and swaps in the returned HTML —
still no full page reload, just an actual network request instead of a
pure client-side toggle.

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

- `core/query.php` — the shared logic, used by both `block/render.php` (the
  first page of each tab, rendered on the initial page load) and the REST
  callback (every page after that): `mlpt_run_tab_query()` builds the
  `WP_Query` for a given tab/page (excluding translation posts via
  `mlp_exclude_translations_meta_query()` if `custom-multilingual-post` is
  active, same as the carousel), `mlpt_render_list_html()` renders the
  `<ul>` of post cards, and `mlpt_render_pagination_html()` renders the
  numbered pagination from `mlpt_page_number_sequence()` — a small
  first/last/current-window-plus-`…` algorithm, not WordPress's own
  `paginate_links()`, since that renders real `<a href>` navigation links
  and these are buttons that fetch over REST instead of navigating.
- `block/render.php` — renders page 1 of both tabs using the shared
  functions above, plus the `role="tablist"` nav. Only the first panel
  starts visible (`hidden` attribute on the rest) — pure progressive
  enhancement, since the HTML is complete and valid even if `tabs.js`
  somehow fails to load, it would just show the "Latest" panel with no way
  to switch tabs or pages.
- `custom-post-tabs.php` registers `/mlpt/v1/posts` (`rest_api_init`) — a
  public, read-only `GET` route (same trust level as core's own
  `/wp/v2/posts`) that validates `tab`/`page`/`per_page` and returns
  `{ tab, page, maxPages, listHtml, paginationHtml }` using the same
  `core/query.php` functions `render.php` uses, so the initial page and
  every later page are built from one definition, not two copies that can
  drift apart. Also registers the `mlpt-thumb` image size on
  `after_setup_theme`, same pattern as `custom-featured-carousel.php`, and
  passes the REST URL to the frontend via `wp_localize_script`
  (`mlptSettings.restUrl`) — `rest_url()` itself already adapts to
  whatever permalink structure the site is using, pretty or plain.
- `block/editor.js` — sidebar UI: just a `RangeControl` for posts-per-tab.
  The canvas preview doesn't try to render real post data (same approach as
  the carousel) — a plain note that the real tabs render on the frontend.
- `assets/tabs.js` — vanilla JS, plain `fetch()` (not `wp.apiFetch`, to
  avoid pulling in its `wp-i18n`/`wp-hooks` dependencies just for one public
  GET on the frontend). Tab switching toggles `.is-active`/`aria-selected`
  and the panel's `hidden` attribute exactly as before; a pagination click
  is handled via one delegated listener per block instance (not one
  listener per button, since a page fetch replaces the pagination buttons
  entirely) that fetches the new page and swaps in the returned
  `listHtml`/`paginationHtml`. Only enqueued on pages that actually use the
  block (`has_block()`).

Visual styling (`.post-tabs`, `.post-tabs__tab`, `.post-tabs__item`, …)
lives in the active theme's stylesheet, same split as every other custom
plugin in this project.

## Files

```
custom-post-tabs.php              top-level MU loader (required by WP itself)
custom-post-tabs/
  custom-post-tabs.php            internal loader — registers the mlpt-thumb image size, the /mlpt/v1/posts REST route, the block, and the frontend JS (only where needed)
  core/
    query.php                     shared query/render logic used by both render.php and the REST route
  block/
    block.json                    block registration (name, attributes: postsPerTab, editorScript, render)
    editor.js                     sidebar UI: posts-per-tab range control
    render.php                    frontend markup (dynamic block, no saved HTML) — page 1 of both tabs
  assets/
    tabs.js                       tab switching + REST-backed pagination
```
