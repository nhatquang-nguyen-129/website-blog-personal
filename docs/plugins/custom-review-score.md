# custom-review-score

**Location:** `public/wp-content/mu-plugins/custom-review-score.php` (loader) + `public/wp-content/mu-plugins/custom-review-score/` (implementation)
**Type:** MU-plugin (always active) that registers a Gutenberg block, its post meta, and a score badge used by the Featured Carousel

## Problem statement

WordPress core has no concept of a review score: no field, no block, no
meta. The end-of-post score box alone *could* be faked with core blocks
(Group + Columns + Heading + Paragraph saved as a pattern), but then the
score is just text inside the post's content. Nothing else on the site
could read it, and the second half of this feature, a score badge on the
post's Featured Carousel slide, needs the score as **structured data**
that the carousel can read without parsing each post's content. No core
block or setting covers that, so this is a genuinely new capability
(`CLAUDE.md` step 3).

## What it is

1. **Review Score block** (`minimal-reader/review-score`, search "Review
   Score" in the inserter): a highlighted box, usually placed at the end of
   a review post. A colored score panel (label + big number) sits on the
   left, and your own summary paragraphs on the right. One per post, and
   posts only: it's hidden from the inserter on pages
   (`allowed_block_types_all`), since the score meta is registered for
   posts and a page would silently drop it on save.
2. **Score badge on the Featured Carousel:** a sticker at the top-left
   corner of the post's slide. A small tilted tag in the tier color, using
   the same text as the box's label (e.g. "Đánh giá game"), overlaps the
   top-left edge of a compact tier-colored plate that hugs the score
   `/10`. `custom-featured-carousel`'s `render.php` calls
   `mlrs_render_badge()`, guarded with `function_exists()`, so removing
   this plugin never fatals the carousel. It just stops showing badges.

   The badge is deliberately carousel-only. Post Tabs, the theme's
   `index.php` lists and core Query Loop show no badge.

Most posts aren't reviews. A post with no score shows no badge.

## Using it

1. In a review post, insert **Review Score** where you want the box
   (typically at the very end).
2. Set the score in the block's sidebar, **Review score** panel (0–10,
   step 0.1). **Reset** clears it.
3. Set the label in the same panel, **Label** (e.g. "Đánh giá game";
   empty means "Đánh giá"). It shows above the number in the box *and* as
   the tilted tag on the carousel badge. Typing stops at **16 characters**
   (spaces included, accents counted as part of their letter). 16 is what
   fits on one line in the tighter of the two spots, the box's score
   panel on desktop (14rem wide, label at 15px uppercase); the carousel
   tag fits far more. An older label already over the limit is kept, with
   a warning, since it would wrap onto two lines in the box.
4. Write the summary in the paragraphs on the right of the box.
   Paragraphs and lists only, no headings (see below).
5. Save/Update as normal. The score is saved together with the post.

Color follows the score:

| Score | Tier (`.mlrs-tier--*`) |
|---|---|
| ≥ 9 | `excellent`, deep red |
| 7 – 8.9 | `good`, green |
| 5 – 6.9 | `mixed`, amber |
| < 5 | `poor`, gray |

## How it works

- **Data:** `_mlrs_score` post meta (`register_post_meta()`,
  `show_in_rest`, `auth_callback` = `edit_post`). It's stored as a
  *string* (`'9.5'`), so `''` unambiguously means "no score". A
  registered number meta reads back as `0` when unset, which can't be told
  apart from an actual 0/10. `mlrs_sanitize_score()` clamps to 0–10 at
  one decimal.
- **Editor:** the block reads/writes that meta through `useEntityProp`
  (same approach as `custom-multilingual-post`'s language dropdown). The
  label is an ordinary block attribute, edited in the sidebar. The summary
  is `InnerBlocks` (paragraph + list only).
- **Label for the badge:** the carousel can't read a block attribute
  without parsing each post's content, so on each save the label is copied
  into `_mlrs_label` post meta (internal, not in REST). A label left at
  its default isn't serialized into the block comment, so nothing is
  stored and `mlrs_get_label()` returns the block's default ("Đánh giá").
- **Frontend box:** a dynamic block. `block/render.php` builds the box;
  `save()` stores only the inner blocks, passed to it as `$content`.
  `assets/review-score.js` (enqueued only on singular pages that contain
  the block) counts the number up from 0 when the box scrolls into view.
  The server-rendered HTML already shows the final number, so with no JS,
  or with `prefers-reduced-motion`, nothing animates and nothing is
  missing.

### The edge cases this design handles

- **Deleting the box removes the badge.** The score and label live in
  meta, separately from the block. `mlrs_sync_meta_with_block()` runs on
  `wp_after_insert_post`: it deletes both whenever the post no longer
  contains the block, and otherwise copies the block's label into meta.
  It's `wp_after_insert_post`, not `save_post`: on a block-editor (REST)
  save, `save_post` fires *before* meta is written, so the score would be
  re-added right after being deleted.
- **Password-protected posts don't leak their score.** Core's password
  gate only covers `the_content()`. The badge reads meta directly, so
  `mlrs_render_badge()` returns `''` while `post_password_required()` is
  true. (The box itself is inside the content, so core already hides it.)
- **Translations keep the score.** `custom-multilingual-post` clones a
  translation *without* its post meta, and `?lang=` swaps that translation
  in on the original's URL. `mlrs_get_score()` falls back to the group's
  original post when a translation has no score of its own. A score is a
  verdict on the thing reviewed, not on the language. `mlrs_get_label()`
  falls back the same way, so a translation can still carry its own
  translated label.
- **The box stays out of the Table of Contents.**
  `custom-table-of-contents` lists every `<h1>`–`<h4>` in the post, so the
  box's label is a `<span>`, and the summary doesn't allow heading blocks.

Visual styling (`.mlrs-box*`, `.mlrs-badge*`, `.mlrs-tier--*`) lives in
the theme, same split as every other plugin here. The badge is sized in
`em`, so its carousel rule only sets `font-size` and the corner offset.
The canvas preview's styles are in the theme's `editor.css`, not
`style.css`. See the theme doc's "Two separate stylesheets" note.

Not included yet: `Review` JSON-LD for Google rich results. It's a
natural next step, since `mlrs_get_score()` already provides the value.

## Files

```
custom-review-score.php              top-level MU loader (required by WP itself)
custom-review-score/
  custom-review-score.php            internal loader: registers the block (explicit editor script deps), enqueues the frontend JS only where the block is used
  core/
    score.php                        _mlrs_score/_mlrs_label meta, mlrs_get_score()/mlrs_get_label() (with translation fallback), tiers, mlrs_render_badge(), block↔meta sync
  block/
    block.json                       block registration (name, attribute: label, usesContext, render)
    editor.js                        canvas preview + sidebar controls for score and label (vanilla JS, no build step)
    render.php                       frontend markup (dynamic block; summary inner blocks arrive as $content)
  assets/
    review-score.js                  count-up of the box's number on scroll into view
```
