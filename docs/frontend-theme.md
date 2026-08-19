# Frontend theme (Substack style: white + orange)

Records the design decisions applied to the frontend, so they don't have to be re-derived from the code on future edits.

## Colors & typography

- All colors are CSS custom properties in [`src/app/(frontend)/globals.css`](../src/app/\(frontend\)/globals.css) (`:root` for light mode, `[data-theme='dark']` for dark mode) — never hardcode colors directly in a component.
- White background (`--background`), warm near-black text (`--foreground`), **orange** as the single accent color (`--primary`, used for buttons, links, category labels).
- Serif font (Source Serif 4, loaded via `next/font/google` in `layout.tsx`, variable `--font-source-serif`) for post titles and post body content (`prose`). Sans font (Geist) for UI/nav/meta text — matches the Substack look.
- Prose/typography config lives in `tailwind.config.mjs` (`theme.extend.typography`).

## Components changed from the original template

- `Header/Component.client.tsx` + `Header/Nav`: from a transparent header floating over the hero → a sticky white header bar, with an orange "Subscribe" button.
- `Footer/Component.tsx`: from a black background → a light, minimal one.
- `components/Card`, `components/CollectionArchive`: from a boxed card grid → a Substack-style post list (serif title, excerpt, meta, thumbnail on the right).
- `heros/PostHero`: from a full-bleed dark background image hero → a white post header with a contained cover image.
- `heros/HighImpact`: removed `-mt-[10.4rem]` (a negative-margin trick that relied on the old transparent header) since the header now always takes up normal space in the layout.
- `components/Logo/Logo.tsx`: from Payload's SVG logo → a text wordmark.
- `components/GoToSite/index.tsx` (new): registered under `admin.components.actions` in `payload.config.ts` — adds a "Go to site" button to the top-right of every admin page, linking to the public site.

## Still placeholders — change before going public

- **Blog name**: the string `"Personal Blog"` hardcoded in `src/components/Logo/Logo.tsx`. Change it in that one spot once you have a real name.
- **"Subscribe" button** in `Header/Nav/index.tsx`: currently points to `/contact` since the site has no real subscribe/newsletter feature yet. Once there's a real form (form-builder or an external email service), update the `href`.
- **Favicon**: still using the template's default `public/favicon.ico`/`favicon.svg` — not yet changed per the roadmap in [CLAUDE.md](../CLAUDE.md#next-steps-customization-roadmap).

## Checking after theme changes

`pnpm dev` doesn't automatically catch typography/color mistakes — after editing `globals.css` or `tailwind.config.mjs`, run `pnpm build` once to make sure the Turbopack production build doesn't break (there was previously a Turbopack + `postcss.config.js` bug on Next 16.3.0, fixed by upgrading to 16.3.1).
