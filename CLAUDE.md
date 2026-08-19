# CLAUDE.md

This file guides Claude Code (or any AI coding assistant) working in this personal blog repo.

## Project overview

This is a personal blog, built on the **Payload Website Template** (Next.js + Payload CMS) — a single codebase containing both the frontend and the CMS/backend, no separate repo.

Direction decisions (see `docs/tech-stack-direction.md` for detailed reasoning, if carried over from the Claude project):
- Want to code the UI myself, own the database, write custom plugins/features, run my own CMS — no external SaaS CMS.
- Post content lives in the **database**, authored through Payload's **admin UI** (not Markdown files + Git).
- Target hosting: **self-managed VPS** (not Vercel/Netlify).
- Priority features: Markdown/MDX rich text (Payload uses Lexical richtext, exportable as blocks), reader comments, SEO, multi-language (Vietnamese/English).

## Getting the initial source

Since the original template uses `workspace:*` inside the Payload monorepo, **don't hand-copy the `templates/website` folder** — always use the official CLI so dependencies resolve to the right versions:

```bash
npx create-payload-app@latest my-blog -t website
cd my-blog
cp .env.example .env
```

## Tech stack

- **Framework**: Next.js (App Router) — both the public site and `/admin` run in one app.
- **CMS**: Payload CMS 3.x — configured entirely in `src/payload.config.ts`, admin panel auto-generated from config, no separate UI code needed.
- **Database**: **Postgres** (`@payloadcms/db-postgres`), used from the start (scaffolded with `create-payload-app ... --db postgres`) instead of the CLI's Mongo default, so local matches the VPS environment.
- **Styling**: Tailwind CSS v4.
- **Rich text**: `@payloadcms/richtext-lexical`.
- **Plugins already included**: `plugin-seo`, `plugin-search`, `plugin-redirects`, `plugin-form-builder`, `plugin-nested-docs`.
- **Reader comments**: not in the template yet — will add Giscus (embed a script into the post block/component), no separate comment backend needed.
- **Multi-language**: use Payload's built-in localization (declare `localization` in `payload.config.ts`, `vi` as default locale + `en`).

## Main directory structure (`src/`)

```
src/
├── app/            # Next.js App Router — (frontend) and (payload) route groups
├── collections/    # CMS definitions: Posts, Pages, Categories, Media, Users
├── Header/         # Global config + component for the header (Payload global)
├── Footer/         # Global config + component for the footer (Payload global)
├── blocks/         # Layout builder blocks (used in Pages/Posts)
├── heros/          # Hero section variants for pages
├── fields/         # Shared fields reused across collections
├── access/         # Access control (who can read/edit what)
├── endpoints/      # Custom REST endpoints
├── hooks/          # Payload hooks (beforeChange, afterChange...)
├── plugins/        # Payload plugin declarations/config
├── providers/      # React context providers for the frontend
├── search/         # plugin-search config
├── components/     # Shared React components for the frontend
└── utilities/      # Utility functions
```

To add a new feature (e.g. a new content type, a field, a custom plugin) → edit/add in `collections/`, `fields/`, `blocks/`, or `plugins/`. To change the UI → edit `app/` and `components/`.

## Common commands

```bash
pnpm install              # install dependencies
pnpm dev                  # run dev server at http://localhost:3000 (admin: /admin)
pnpm build                # production build (runs payload build)
pnpm generate:types       # regenerate TypeScript types from Payload config after changing a collection/field
pnpm lint                 # run lint
pnpm lint:fix             # auto-fix lint errors
```

After every change to `collections/` or a field in `payload.config.ts`, always rerun `pnpm generate:types` so `payload-types.ts` stays correct.

## Environment variables (`.env`)

```
DATABASE_URL=            # Postgres connection string
PAYLOAD_SECRET=          # secret used to encrypt JWTs — never commit the real value
NEXT_PUBLIC_SERVER_URL=  # public URL of the site, e.g. http://localhost:3000 in dev
CRON_SECRET=             # protects cron jobs (scheduled publish)
PREVIEW_SECRET=          # protects the live preview route
```

Never commit a real `.env` file to Git — only commit `.env.example`.

## Next steps (customization roadmap)

1. Update branding: real site name (currently placeholder "Personal Blog" in `components/Logo/Logo.tsx`), favicon, description.
2. Add `vi`/`en` localization in `payload.config.ts`, mark which fields need translation (`localized: true`).
3. Add Giscus to the post-detail template (a dedicated component in `components/`, embedded in the Post detail page).
4. Review/tune `plugin-seo` for actual needs (default meta, OG image).
5. Write the VPS deploy process: build → run Next.js with PM2 (since there's a server/API part, not a static site) → reverse proxy via Nginx/Caddy → dedicated Postgres on the VPS or a managed DB.

## Code conventions

- Prefer editing `src/collections`, `src/blocks`, `src/fields` when adding CMS features — avoid editing the `payload`/`@payloadcms/*` packages directly (those are dependencies, not our code).
- Keep all data-access logic going through the Payload Local API (`payload.find`, `payload.create`...) with `overrideAccess: false` when running in a user context, so access control isn't bypassed.
- After changing a schema (field/collection), always run `pnpm generate:types` before committing.
