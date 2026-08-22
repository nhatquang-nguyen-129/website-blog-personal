# Go-live plan: VPS deployment & CI/CD

A concrete plan for taking this blog from local dev to a live, publicly reachable site on a self-managed VPS, plus a CI/CD pipeline so future pushes deploy automatically. This fleshes out roadmap item 5 in [CLAUDE.md](../CLAUDE.md#next-steps-customization-roadmap).

## 1. Target architecture

```
GitHub repo (main branch)
   │  push / merge
   ▼
GitHub Actions CI  ──lint, typecheck, build──▶  (PR checks)
   │  on merge to main
   ▼
GitHub Actions CD  ──SSH──▶  VPS
                              ├─ git pull + pnpm install + pnpm build
                              ├─ payload migrate (Postgres schema)
                              └─ pm2 reload (zero-downtime restart)

VPS (Ubuntu 24.04)
   ├─ Next.js app, managed by PM2, listening on 127.0.0.1:3000
   ├─ PostgreSQL 16, local, not exposed publicly
   ├─ Caddy — reverse proxy + automatic HTTPS (Let's Encrypt)
   └─ ufw firewall — only 22/80/443 open

Domain DNS ──A record──▶ VPS public IP
```

Everything runs on one VPS to start (matches the "own the database, own the server" direction in CLAUDE.md). Object storage and a managed database are called out below as optional upgrades, not required to go live.

## 2. What to buy / provision

| Item | Purpose | Suggested option | Cost |
|---|---|---|---|
| Domain name | e.g. `yourblog.com` | Cloudflare Registrar (at-cost, no markup) or Namecheap | ~$10–15/yr |
| VPS | Runs the app + Postgres | Hetzner Cloud CX22 (2 vCPU / 4GB RAM) or DigitalOcean/Vultr equivalent | ~$5–7/mo |
| Email sending | Payload transactional emails (password reset, etc.) | Resend (3k emails/mo free) or Brevo | Free tier |
| SSL certificate | HTTPS | Let's Encrypt via Caddy — automatic, free | $0 |
| Uptime monitoring *(optional)* | Alert if the site goes down | UptimeRobot | Free tier |
| Object storage *(optional)* | Durable media storage, survives VPS loss | Cloudflare R2 (10GB free) | Free tier to start |
| Error tracking *(optional)* | Catch production errors | Sentry | Free tier |

**Rough total to go live: ~$70–100/year** (domain + VPS), everything else has a workable free tier.

Skip a managed Postgres provider (Neon/Supabase/Railway) unless you specifically want to offload database ops — self-hosting Postgres on the same VPS is cheaper and keeps this project's "own the database" direction from CLAUDE.md.

## 3. Domain & DNS

1. Buy the domain.
2. Point an `A` record at the VPS's public IPv4 (and `AAAA` if the VPS has IPv6).
3. Optional: put the domain behind Cloudflare (DNS only, not proxied) for easier DNS management. If you do proxy through Cloudflare, keep SSL mode set to **Full (strict)** so Caddy's cert is still validated end-to-end.

## 4. VPS provisioning & hardening

1. Create the VPS, Ubuntu 24.04 LTS.
2. Create a non-root user with `sudo`, add your SSH public key, disable password auth and root SSH login in `sshd_config`.
3. `ufw allow 22,80,443/tcp && ufw enable`.
4. Install `fail2ban` for SSH brute-force protection.
5. Enable unattended security upgrades (`unattended-upgrades` package).

## 5. Install the stack on the VPS

```bash
# Node.js 20 LTS + pnpm
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
corepack enable && corepack prepare pnpm@9 --activate

# PostgreSQL 16
sudo apt install -y postgresql-16

# PM2 (process manager)
sudo npm install -g pm2

# Caddy (reverse proxy + automatic HTTPS)
sudo apt install -y caddy
```

Create the production database, matching the pattern already used locally (see [docs/local.md](./local.md)):

```bash
sudo -u postgres psql -c "CREATE ROLE bloguser WITH LOGIN PASSWORD '<strong-random-password>';"
sudo -u postgres psql -c "CREATE DATABASE blog OWNER bloguser;"
```

## 6. First deploy (manual, one time)

```bash
git clone https://github.com/<you>/website-blog-personal.git
cd website-blog-personal
pnpm install
cp .env.example .env   # fill in real production values, see §7
pnpm build
pm2 start "pnpm start" --name blog
pm2 save
pm2 startup            # prints a systemd command — run it so PM2 survives reboots
```

Caddyfile (`/etc/caddy/Caddyfile`):

```
yourblog.com {
    reverse_proxy 127.0.0.1:3000
}
```

`sudo systemctl reload caddy` — HTTPS is issued and renewed automatically, no manual cert handling.

Then visit `https://yourblog.com/admin` and go through the same "create your first user" flow described in [docs/local.md](./local.md#5-run-the-dev-server) — no default account is ever hardcoded.

## 7. Environment variables for production

| Variable | Production value |
|---|---|
| `PORT` | `3000` (internal only, Caddy proxies to it) |
| `DATABASE_URL` | `postgresql://bloguser:<password>@127.0.0.1:5432/blog` |
| `PAYLOAD_SECRET` | new long random string, **different from local** |
| `NEXT_PUBLIC_SERVER_URL` | `https://yourblog.com` |
| `CRON_SECRET` | new random string |
| `PREVIEW_SECRET` | new random string |
| Email adapter key | from Resend/Brevo, once an email adapter is configured (see [CLAUDE.md](../CLAUDE.md) roadmap) |

Never commit this `.env` — it lives only on the VPS (and as encrypted secrets in GitHub Actions for the CD step, §9).

## 8. Database migrations in production

Local dev auto-pushes schema changes to Postgres on every save — fine for iterating, not safe for production. Before the first production deploy:

1. Run `pnpm payload migrate:create` locally once you're ready to lock in a schema baseline.
2. Commit the generated migration files.
3. On every deploy, run `pnpm payload migrate` on the VPS **before** restarting the app (see the CD workflow in §9) — this applies schema changes deliberately instead of relying on dev's auto-push.

## 9. CI/CD with GitHub Actions

**CI** (`.github/workflows/ci.yml`) — runs on every PR:

```yaml
name: CI
on: [pull_request]
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: pnpm/action-setup@v4
        with: { version: 9 }
      - uses: actions/setup-node@v4
        with: { node-version: 20, cache: pnpm }
      - run: pnpm install
      - run: pnpm generate:types && git diff --exit-code src/payload-types.ts
      - run: pnpm build
```

The `generate:types` + `git diff --exit-code` step catches schema changes committed without regenerating `payload-types.ts` — enforces the rule already stated in CLAUDE.md.

**CD** (`.github/workflows/deploy.yml`) — runs on merge to `main`:

```yaml
name: Deploy
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.VPS_HOST }}
          username: ${{ secrets.VPS_USER }}
          key: ${{ secrets.VPS_SSH_KEY }}
          script: |
            cd website-blog-personal
            git pull origin main
            pnpm install
            pnpm build
            pnpm payload migrate
            pm2 reload blog
```

Add `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY` (a dedicated deploy key, not your personal one) as encrypted repo secrets in GitHub. `pm2 reload` restarts with zero downtime.

Once this is set up, the workflow becomes: **merge to `main` → live in ~1 minute**, no manual VPS access needed for routine deploys.

## 10. Media storage

Uploads default to local disk on the VPS (`public/media`, gitignored — see the [README](../README.md#repository-scope)). That's fine to start, but it has no redundancy: losing the VPS loses the media.

Upgrade path when it matters: swap in `@payloadcms/storage-s3` pointed at Cloudflare R2 (S3-compatible, 10GB free), so uploads live in object storage instead of the VPS disk. Not required to go live — just note it as a follow-up.

## 11. Backups

```bash
# daily cron: dump Postgres and ship it off the VPS
0 3 * * * pg_dump -U bloguser blog | gzip > /backups/blog-$(date +\%F).sql.gz
```

Copy backups off the VPS regularly (e.g. `rclone` to R2, or download to your machine) — a backup that only lives on the same VPS doesn't protect against VPS loss. If media stays on local disk (§10), back that up too.

## 12. Monitoring

- `pm2 logs blog` / `pm2 monit` for live app logs and resource usage.
- UptimeRobot (or similar) pinging `https://yourblog.com` — free tier, alerts by email if the site goes down.
- Optional: Sentry for catching and triaging runtime errors in production.

## 13. Go-live checklist

- [ ] Buy domain
- [ ] Provision VPS, harden it (§4)
- [ ] Install Node/pnpm/Postgres/PM2/Caddy (§5)
- [ ] Create production Postgres role + database
- [ ] First manual deploy + `pm2 startup` (§6)
- [ ] Point DNS at the VPS, verify HTTPS via Caddy
- [ ] Create the first admin user through `/admin`
- [ ] Lock in a migration baseline, switch off dev auto-push (§8)
- [ ] Set up CI + CD GitHub Actions workflows (§9)
- [ ] Set up automated Postgres backups shipped off-VPS (§11)
- [ ] Set up uptime monitoring (§12)
- [ ] Smoke test: home page, a post, `/admin` login, image upload
