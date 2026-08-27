# Domain and DNS

## Buying a domain

Any registrar works (Namecheap, GoDaddy, Vietnamese registrars like
Mắt Bão/PA Vietnam/iNET, or the hosting provider itself if they sell
domains too). Nothing here is WordPress-specific — the only thing that
matters for the steps below is that you end up with access to that
domain's **DNS settings**, wherever they're managed.

## Pointing the domain at the hosting

Two ways to do this — pick one, don't mix them:

### Option A — Delegate to the host's nameservers (simpler)

The hosting provider gives you nameservers (something like
`ns1.yourhost.com` / `ns2.yourhost.com`, found in the cPanel welcome email
or panel). Set those as your domain's nameservers at the registrar. DNS
records (A record, MX, etc.) are then managed **in cPanel**, not at the
registrar — cPanel auto-creates the right A record pointing at itself when
you add the domain there.

Simplest option if you don't need any DNS-managed services outside the
hosting (no separate email provider, no CDN in front). Good default for a
personal blog.

### Option B — Keep DNS at the registrar (or a separate DNS provider like Cloudflare), point records manually

Keep the registrar's (or Cloudflare's) nameservers, and add records
yourself:

| Type | Host | Value | Purpose |
|---|---|---|---|
| A | `@` | the hosting server's IP address | `yourdomain.com` → hosting |
| CNAME | `www` | `yourdomain.com` | `www.yourdomain.com` → same site |

Use this option if you want Cloudflare's proxy/CDN in front, or you're
already using that registrar/provider's DNS for email (MX records) and
don't want to migrate those too.

DNS propagation can take anywhere from a few minutes to ~24 hours
(depends on your previous DNS's TTL). `dig yourdomain.com` or
`nslookup yourdomain.com` from a terminal shows whether it's resolved to
the hosting IP yet.

## Once it resolves: SSL and the WordPress site URL

1. In cPanel, add the domain (if not done automatically) and run
   **AutoSSL** (or the host's equivalent) to issue a free Let's Encrypt
   certificate. Do this *before* finishing the WordPress install so the
   site address can be set to `https://` from the start — changing a live
   site's protocol after the fact means also updating `siteurl`/`home` in
   the database (`wp-admin` → Settings → General, or directly via SQL if
   the admin screen is unreachable).
2. Force HTTPS: most cPanel setups have a "Force HTTPS Redirect" toggle
   per domain (SSL/TLS Status page) — turn it on rather than hand-writing
   `.htaccess` redirect rules.

Next: `initial-deploy.md` to actually get WordPress running at that
domain.
