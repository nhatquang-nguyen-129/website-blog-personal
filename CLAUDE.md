# CLAUDE.md

Guidance for working on this repo's WordPress code (`public/`). Read this
before adding or changing anything under `public/wp-content/`.

## The one rule everything else follows

**WordPress core stays generic and untouched. All customization lives in
the theme or a plugin — never in `wp-admin/`, `wp-includes/`, or any root
PHP file.** Core is disposable: the `Dockerfile` downloads it fresh on every
build and nothing in it is git-tracked (see `.gitignore`). If a change
requires editing a core file to work, that's a sign the approach is wrong,
not that core needs an exception.

This has been verified in practice: `wp-admin/`, `wp-includes/`, and every
root PHP file in this project are byte-for-byte identical to a stock
WordPress download. Keep it that way.

## Before writing a new feature, check in this order

1. **Does WordPress core already do this, configurable from the admin UI?**
   Use it as-is. Don't rebuild it.
   - Site title/tagline/logo → Settings → General / Customizer
     (`bloginfo()`, `the_custom_logo()`), not a hardcoded string or a new
     option.
   - Navigation → `register_nav_menus()` + `wp_nav_menu()`, edited under
     Appearance → Menus — not a hand-maintained list of links in a
     template.
   - RSS/subscribe → `get_feed_link()` — WordPress already generates and
     serves feeds; don't build a mailing-list mechanism to imitate it.
   - Login → `wp_login_url()`, `is_user_logged_in()` — don't roll a custom
     auth screen.
   - Post metadata (date/author/categories/tags on a post) → the core
     **Post Date** / **Post Author** / **Post Terms** blocks, inserted by
     whoever's editing that post. Do **not** hardcode a byline/date/category
     line into a template — that renders on every post whether the person
     writing it wanted it there or not, and duplicates a block WordPress
     already ships. (This exact mistake was made and reverted once in
     `single.php` — see `docs/themes/custom-minimal-reader.md`.)
   - A list of posts → the core **Query Loop** block (`core/query` +
     `core/post-template`), configurable per-instance (count, order,
     filters, which fields show) entirely from the block's own UI. Don't
     hand-write a `WP_Query` + loop in a template for something an editor
     should be able to reconfigure without touching code.
   - A list of categories → the core **Categories** block, not a custom
     `wp_list_categories()`-based template section.
   - Sticky/pinned content → WordPress's native Sticky Posts feature
     (Posts → Quick Edit → "Stick to the top of the blog") *if* that's
     genuinely what's needed. (`custom-featured-carousel` deliberately does
     **not** use this — see its doc for why manual post-picking won out for
     that specific feature. Sticky Posts is still the right tool for
     anything that actually wants "pin this to the top of the normal blog
     loop.")
2. **Is this presentation or behavior?**
   - Presentation (colors, type, spacing, how something *looks*) → the
     **theme** (`minimal-reader`). Never ship CSS from a plugin for
     anything other than its own admin-only editor UI (see
     `docs/themes/custom-minimal-reader.md`'s note on `editor.css` vs. a plugin's own
     `enqueue_block_editor_assets`-loaded stylesheet).
   - Behavior/data/a genuinely new capability WordPress doesn't have → a
     **plugin** (an MU-plugin under `wp-content/mu-plugins/`, following the
     structure below). Never bake feature logic into the theme — a theme
     swap should never delete a feature, only its styling.
3. **Does it actually not exist in core or a block already?** Only now
   write custom code. Every plugin in `docs/plugins/` exists because there
   is no WordPress-native equivalent: a same-URL multilingual toggle, an
   auto-generated table of contents block, a site-wide maintenance toggle,
   an auto-advancing carousel. None of them duplicate something core
   already offers.

## Plugin conventions

- Name: `custom-<what-it-does>` (e.g. `custom-table-of-contents`) — makes
  hand-written plugins instantly distinguishable from anything that might
  later get installed from WordPress.org. Both the top-level loader file
  (`custom-x.php`) and its implementation folder (`custom-x/`) use this
  name.
- One doc file per plugin in `docs/plugins/custom-x.md`, written when the
  plugin is. Leads with a **Problem statement** section: what's missing,
  confirmation that core (and, where relevant, a core block or a
  WordPress.org plugin) doesn't already cover it, and why — the same
  check-in-this-order reasoning from the top of this file, made explicit
  and permanent rather than left implicit in the commit that added the
  plugin. Followed by what it is, how to use it, how it works, file list —
  see any existing one for the expected shape.
- REST endpoints, not `admin-ajax.php` + hand-rolled nonces, for anything
  new — pair `register_rest_route()` with a real `permission_callback`.
  `wp.apiFetch` already handles nonces for you.
- Editor UI (blocks, sidebar panels) is plain `wp.element.createElement` —
  no JSX, no build step, nothing under `node_modules/`. If a block needs
  editor script dependencies, register them explicitly with
  `wp_register_script()` (`wp-blocks`, `wp-element`, `wp-block-editor`,
  etc.) rather than relying on `block.json`'s bare `editorScript`, which
  only picks up dependencies from a build-tool-generated `editor.asset.php`
  that won't exist here.
- A plugin's frontend output uses its own semantic class names
  (`.mlp-language-switcher`, `.mlptoc`, `.home-carousel`) and does not
  enqueue frontend CSS for them — that's the theme's job (below).

## Theme conventions

- Classic PHP theme (`header.php`, `footer.php`, `single.php`, …) — not
  Full Site Editing. See `docs/themes/custom-minimal-reader.md` for the full reasoning and the
  file-by-file breakdown.
- The homepage (and any Page using the "Blank Canvas" template) is meant to
  be assembled from blocks in the editor, not hardcoded in PHP. If asked to
  change what the homepage shows, look at whether it's a block-editor job
  before reaching for a template change.
- Styles every custom plugin's frontend markup (`.mlptoc`, `.home-carousel`,
  etc.) and every core block used on a blank-canvas page
  (`.wp-block-post-title`, `.wp-block-categories-list`, …), so a page built
  purely from blocks already matches the site.
- Two separate editor stylesheets exist for a real reason — read the
  "⚠️ Two separate stylesheets" section of `docs/themes/custom-minimal-reader.md` before adding
  `add_editor_style()` CSS or a block's own sidebar CSS.

## Where to look for more detail

- `docs/themes/custom-minimal-reader.md` — the theme's design system, template responsibilities,
  the editor-CSS gotcha, and why "Settings → Reading → A static page"
  matters for the homepage.
- `docs/plugins/*.md` — one per custom plugin.
- `docs/deploy/` — hosting, domain/DNS, and how a `git push` reaches
  production. The same "core stays untouched, only `wp-content/mu-plugins/`
  and `wp-content/themes/minimal-reader/` are ours" rule applies there too —
  a deploy is never a wholesale copy of the repo onto the server.
- `CHANGELOG.md` — what shipped and why, in the order it happened.
