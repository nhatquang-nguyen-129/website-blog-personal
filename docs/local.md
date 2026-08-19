# Running and deploying locally

Guide for the first-time setup, running the dev server, and simulating a VPS-like environment on your local machine.

## Requirements

- Node.js LTS (>= 20)
- pnpm 9 (`corepack prepare pnpm@9 --activate` — pnpm 10+ requires Node 22, not yet compatible with Node 20)
- PostgreSQL locally (used from the start, no Mongo detour — see [CLAUDE.md](../CLAUDE.md#tech-stack))

## 1. Getting the source for the first time

The original template uses `workspace:*` inside the Payload monorepo — **don't hand-copy** the `templates/website` folder. Always use the official CLI so dependencies resolve to the right version, specifying the Postgres adapter from the start:

```bash
npx create-payload-app@latest -n my-blog -t website --db postgres
cd my-blog
```

The CLI auto-generates `.env` with `DATABASE_URL` already pointing at Postgres.

If the repo already exists (already has `src/`), just clone this repo and install dependencies in step 2.

## 2. Install dependencies

```bash
pnpm install
```

If you hit a `packages field missing or empty` error, check that `pnpm-workspace.yaml` declares `packages: ['.']` — that file only exists to declare `allowBuilds`, it's not a real monorepo.

## 3. Configure `.env`

```
PORT=3000
DATABASE_URL=postgresql://postgres:postgres@127.0.0.1:5432/blog
PAYLOAD_SECRET=<long random string, local-only>
NEXT_PUBLIC_SERVER_URL=http://localhost:3000
CRON_SECRET=<random string>
PREVIEW_SECRET=<random string>
```

`PORT=3000` keeps `pnpm dev`/`pnpm start` fixed on port 3000, instead of Next.js hopping to a different port if 3000 was ever taken.

Never commit a real `.env` file to Git — only commit `.env.example`.

## 4. Run the local database

Install PostgreSQL via Homebrew (macOS):

```bash
brew install postgresql@16
brew services start postgresql@16
```

Create a role and database matching the `DATABASE_URL` above:

```bash
psql -d postgres -c "CREATE ROLE postgres WITH LOGIN SUPERUSER PASSWORD 'postgres';"
psql -d postgres -c "CREATE DATABASE blog OWNER postgres;"
```

(With Docker instead: `docker run -d -p 5432:5432 -e POSTGRES_PASSWORD=postgres postgres:16`, then create the `blog` database yourself.)

## 5. Run the dev server

```bash
pnpm dev
```

- Frontend: http://localhost:3000
- Admin panel: http://localhost:3000/admin

When the database is still empty (no users — exactly the state after the first VPS deploy), `/admin` automatically shows the **"Create your first user"** form. Fill in your real email/password there to create the admin — no default account is hardcoded anywhere in the code. From the second user onward (or once the first user already exists), `/admin` just shows a normal login screen. Editing admin info (changing email/password) later happens in the admin's **Users** menu.

Next.js 16 auto-injects an "agent rules" block into `CLAUDE.md` every time `next dev` runs. This repo has disabled that (`agentRules: false` in `next.config.ts`) to keep `CLAUDE.md` fully author-controlled.

### Seeding sample data (optional)

If you want a few sample posts/pages to look at instead of starting empty, after creating the first admin user via `/admin`, log in and call:

```bash
curl -X POST http://localhost:3000/next/seed -b cookies.txt
```

(needs a valid login session cookie — log in via `/api/users/login` and use the returned cookie, or call it directly from the browser once logged into admin).

This endpoint **wipes** all existing posts/pages/categories and re-inserts sample data — don't run it against real data you're using.

## 6. Live reload while developing

`pnpm dev` already gives you live/hot reload out of the box — no extra setup needed. Keep the dev server running and a browser tab open at `http://localhost:3000`; on every file save, Next.js recompiles and the browser updates automatically (Fast Refresh) within about a second, usually preserving component state. Server-side or config changes (e.g. `payload.config.ts`, `next.config.ts`) still auto-reload, just with a full page refresh instead of a hot patch — you'll see `✓ Compiled` / `Restarting the server...` in the terminal when that happens.

The `⨯ turbopackServerFastRefresh` line in the startup log is an unrelated, still-experimental Next.js 16 flag for optimizing the dev server's own recompile speed — it has no effect on whether the browser hot-reloads, so it's safe to ignore.

## 7. Common commands during development

```bash
pnpm generate:types   # required after changing a collection/field in payload.config.ts
pnpm lint             # run lint
pnpm lint:fix         # auto-fix lint errors
pnpm build            # production build, verify before deploying
```

Always run `pnpm generate:types` and commit `payload-types.ts` together with schema changes — see [CLAUDE.md](../CLAUDE.md#common-commands).

## 8. Verifying a local production build (simulating the VPS)

Before deploying, build and run in production mode locally first:

```bash
pnpm build
pnpm start
```

Next.js runs at http://localhost:3000 by default. This is also how the VPS will run the app (via PM2), so if this step fails, deploy will fail too.

## Troubleshooting

- **Blank admin panel / DB connection error**: check `DATABASE_URL` in `.env` and make sure Postgres is running (`pg_isready`).
- **Type errors after changing a collection**: run `pnpm generate:types`, then restart the dev server.
- **Port 3000 already in use**: temporarily use `PORT=3001 pnpm dev`, or kill whatever process holds that port (`lsof -i :3000`).
- **`pnpm install` errors about Node.js version with pnpm 10+**: run `corepack prepare pnpm@9 --activate` to pin pnpm 9, which is compatible with Node 20.
