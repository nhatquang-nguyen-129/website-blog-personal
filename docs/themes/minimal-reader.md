# minimal-reader (theme)

**Location:** `public/wp-content/themes/minimal-reader/`
**Type:** Classic PHP theme (no Full Site Editing, no build tooling — no Node/webpack anywhere in it)

## Philosophy

- **Classic, not block-based/FSE.** Templates are plain PHP (`header.php`,
  `footer.php`, `single.php`, …), not `templates/*.html` + `theme.json`.
  This was a deliberate choice: FSE would mean rewriting the whole theme as
  block templates for a marginal gain, since the things that actually
  benefit from being editable (the homepage, a carousel) are already
  block-based without it — see "The homepage is a blank canvas" below.
- **Theme = presentation, plugins = features.** Every custom plugin in
  `docs/plugins/` (multilingual, table of contents, featured carousel,
  maintenance mode) outputs plain HTML with its own semantic class names
  (`.mlp-language-switcher`, `.mlptoc`, `.home-carousel`, …) and does zero
  styling itself. This theme's stylesheet is what makes them look like part
  of the site. Swap the theme later and those plugins still *work* — they'd
  just need re-styling, not rebuilding.
- **No build step.** Fonts load from Google Fonts by `<link>`, there's no
  Sass/PostCSS, and the two small interaction scripts (`theme-toggle.js`,
  `header-interactions.js`) are hand-written vanilla JS enqueued directly.

## Design system

Matches the earlier Payload/Next.js frontend's look on purpose (see git
history on `branch_2x`): warm white/near-black surfaces, one burnt-orange
accent, serif body copy.

- Colors and fonts are CSS custom properties defined on `:root` in
  `assets/css/style.css` (`--background`, `--foreground`, `--primary`,
  `--secondary`, `--muted-foreground`, `--border`, `--radius`, plus
  `--font-sans` / `--font-serif` / `--font-mono`), redefined under
  `[data-theme='dark']` for dark mode.
- **Dark mode** is a `data-theme` attribute on `<html>`, not a media query
  alone. An inline script at the very top of `header.php`'s `<head>` reads
  `localStorage['mlr-theme']` (falling back to `prefers-color-scheme`) and
  sets the attribute *before* anything paints. `assets/css/style.css` hides
  `<html>` (`opacity: 0`) until that attribute exists, to avoid a flash of
  the wrong theme. The sun icon button in the header (`assets/js/theme-toggle.js`)
  flips the attribute and persists the choice.
- Fonts: Source Serif 4 (headings, article body — loaded from Google
  Fonts) and a system-UI stack for everything else (nav, buttons, UI
  chrome).

### ⚠️ Two separate stylesheets, on purpose

`assets/css/style.css` is the frontend stylesheet — but it is **not** also
used for the block editor's `add_editor_style()`. A dedicated
`assets/css/editor.css` exists for that, containing only the tokens and
content typography actually useful for previewing a post. This split exists
because of a real bug: `style.css`'s flicker-prevention rule
(`html { opacity: 0 }`, only undone by the inline script above, which never
runs inside the editor's content iframe) left the whole editor canvas
permanently invisible when it was briefly used there. If you need a style to
apply inside the post/page editor's content preview, add it to `editor.css`,
not `style.css`.

Separately, a block's own **sidebar** UI (`InspectorControls`) renders in
the *outer* editor chrome, not that iframe at all — `add_editor_style()`
can't reach it either way. `custom-featured-carousel` is the example: its
sidebar CSS is its own file, enqueued by the plugin itself via
`enqueue_block_editor_assets`, not by this theme.

## Templates

```
header.php                        <head>, dark-mode init script, the masthead (see below)
footer.php                        footer nav + copyright, wp_footer(), closing tags
index.php                         blog listing / category / tag / search results (post-list cards)
single.php                        one post: title, featured image, [language_switcher] if translated, content
page.php                          one page: title, content — no byline, no featured-image treatment
page-templates/template-home.php  "Blank Canvas" template — see below
functions.php                     theme setup, asset enqueuing, excerpt tweaks
```

There is deliberately no author/date/category line auto-rendered on
`single.php` — that used to be hardcoded and was removed. If you want that
information on a post, add WordPress's own **Post Date** / **Post Author** /
**Post Terms** blocks to the post's content in the editor; nothing renders
automatically that you didn't explicitly place there.

### Header

`header.php` builds a Substack-style masthead:

- Site title, centered independent of whatever's in the actions row
  (absolutely positioned + `transform: translateX(-50%)`, not a 3-column
  grid — simpler, no empty placeholder column needed). Falls back to normal
  left-aligned flow under 40rem so it can't collide with the actions on
  narrow screens.
- Search and Share icon buttons on the right, a **Subscribe** link that
  points at the site's native RSS feed (`get_feed_link()` — no newsletter
  plugin, this already works), and **Sign in** (`wp_login_url()`, hidden
  once you're actually logged in), plus the dark-mode toggle.
  `assets/js/header-interactions.js` wires up both:
  - **Search** toggles a hidden panel containing the theme's own
    `get_search_form()`.
  - **Share** opens a dropdown menu (modeled on Substack's), not the
    native Web Share sheet — `data-share-menu` in `header.php`, toggled by
    `data-share-toggle`, closed on outside click or <kbd>Escape</kbd>. Each
    item is a plain button with `data-share-action`, resolved client-side
    against `window.location.href` / `document.title` at click time (no
    server-rendered share URLs to keep in sync):
    - **Copy link** — `navigator.clipboard.writeText()`, with the label
      swapping to "Copied!" briefly (menu stays open so that's visible).
    - **Send as email** — a plain `mailto:` link.
    - **Facebook / LinkedIn / X** — that platform's public share-intent URL
      (`…/sharer/sharer.php?u=`, `…/sharing/share-offsite/?url=`,
      `twitter.com/intent/tweet?...`) opened in a popup window. No API
      keys or app registration needed — these are just plain GET URLs.
    - **Bluesky** — same idea, `bsky.app/intent/compose?text=`.
    - Deliberately **not** included: Substack's "Get shareable images" and
      "Share to Notes" — both are features of Substack itself with no
      WordPress equivalent, not something to fake.
- A nav row below, fed by the **Primary Menu** location
  (`register_nav_menus()` in `functions.php` — assign one under
  Appearance → Menus; the row simply doesn't render via `has_nav_menu()`
  until you do). The current page's link gets a bottom border automatically
  via WordPress's own `current-menu-item` class.

### The homepage is a blank canvas

`page-templates/template-home.php` ("Blank Canvas" in the Template picker)
renders nothing but `the_content()` — no hardcoded hero, no hardcoded feed.
Assign it to a Page (Settings → Reading → set that Page as the homepage),
then build the homepage entirely with blocks, editable and rearrangeable
like any other page:

- Intro text → core **Paragraph**/**Heading**.
- Category nav → core **Categories** block.
- Posts feed → core **Query Loop** (count, order, filters, per-post fields —
  all in the block's own UI).
- A pinned-posts spotlight → the **Featured Carousel** block
  (`custom-featured-carousel` plugin) — see its own doc for how posts are
  picked and reordered.

This theme ships default styling for the core blocks above
(`.wp-block-post-title`, `.wp-block-categories-list`,
`.wp-block-post-template`, …) so a page assembled purely from them already
matches the site, with no theme-specific class names required. See the
"Core blocks" section of `assets/css/style.css`.

`.page-canvas` (the template's wrapper) supports `alignwide`/`alignfull` on
any block inside it — that's what lets the carousel break out to full-bleed
width while normal content stays in a readable column.

#### Why "A static page" matters (Settings → Reading)

The blocks above — Featured Carousel included — live inside one specific
**Page's** content (whichever Page you assigned the "Blank Canvas" template
to, e.g. "Home"). WordPress only renders a Page's content on the site's
front page (`/`) when **Settings → Reading → Your homepage displays** is
set to **"A static page"**, with that Page picked as the Homepage. This is
plain WordPress behavior, not anything specific to this theme or plugin:

- **A static page** — `/` renders that Page's actual content, so whatever
  blocks are on it (intro, Categories, Query Loop, Featured Carousel) show
  up.
- **Your latest posts** — `/` instead renders the classic chronological
  blog loop (`index.php`), which has nothing to do with any Page's content.
  The Home page (and the carousel inside it) still exists and would render
  fine if visited at its own URL — it just isn't what's showing at `/`
  anymore.

So "the carousel doesn't show" after switching to "Your latest posts" isn't
a bug in the block — it's that the page containing it is no longer the one
being displayed at the site's root URL. This applies to *any* block placed
on that Page, not just the carousel.

## Plugin integration points

- `[language_switcher]` shortcode (from `custom-multilingual-post`) is
  echoed directly in `single.php` when the post is part of a translation
  group. Styled as `.mlp-language-switcher`.
- The Table of Contents block's `.mlptoc` markup and the Featured
  Carousel's `.home-carousel` markup are both styled here, not in their
  respective plugins — see the "Theme = presentation, plugins = features"
  note above.

## Extending this theme

- **New page template:** drop a file in `page-templates/` with a
  `Template Name:` doc comment — WordPress scans that directory
  automatically (no manual registration needed).
- **New core-block styling:** add to the "Core blocks" section of
  `style.css`, targeting the block's own class (`.wp-block-*`) rather than
  inventing a new one, so it applies wherever that block is used, not just
  on the homepage.
- **New dark-mode-aware color:** add the light value under `:root` and the
  dark override under `[data-theme='dark']` — never hardcode a color
  outside those two places, or dark mode will silently miss it.
