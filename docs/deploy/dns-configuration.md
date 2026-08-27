# DNS configuration

Assumes a domain already bought — see `domain-registration.md` if not yet.

## Pointing the domain at the hosting

Two ways to do this — pick one, don't mix them:

### Option A — Delegate to the host's nameservers (simpler)

The hosting provider gives you nameservers (something like
`ns1.yourhost.com` / `ns2.yourhost.com`, found in the cPanel welcome email
or panel — on 1Panel/iNET see `inet-onepanel-setup.md`). Set those as your
domain's nameservers at the registrar. DNS records (A record, MX, etc.) are
then managed **in the hosting panel**, not at the registrar — it
auto-creates the right A record pointing at itself when you add the domain
there.

Simplest option if you don't need any DNS-managed services outside the
hosting (no separate email provider, no CDN in front). Good default for a
personal blog.

### Option B — Keep DNS at the registrar (or a separate DNS provider like Cloudflare), point records manually

Keep the registrar's (or Cloudflare's) nameservers, and add records
yourself. This project's own domain+hosting (both at iNET) uses this
option — see `inet-dns-setup.md` for the exact portal steps and iNET's own
confirmed propagation time, since iNET's official guide doesn't offer
nameserver delegation as an option at all.

| Type | Host | Value | Purpose |
|---|---|---|---|
| A | `@` | the hosting server's IP address | `yourdomain.com` → hosting |
| CNAME | `www` | `yourdomain.com` | `www.yourdomain.com` → same site |

Why both records, not just the A record: `yourdomain.com` (the "root" or
"apex" domain, host `@`) and `www.yourdomain.com` are two distinct hostnames
as far as DNS is concerned — nothing makes one resolve from the other
automatically. Without the CNAME, a visitor who types `www.yourdomain.com`
gets no site at all (`NXDOMAIN`), even though the bare domain works fine.
The CNAME points `www` at the root domain instead of duplicating the same IP
in a second A record, so if the hosting server's IP ever changes, there's
only one record (the A record) to update — the CNAME keeps following it
automatically. Root-domain records technically can't be a CNAME per the DNS
spec (a zone apex can't have a CNAME alongside its other required records,
like MX), which is why the root uses an A record and only `www` uses a
CNAME, never the other way around.

Use this option if you want Cloudflare's proxy/CDN in front, or you're
already using that registrar/provider's DNS for email (MX records) and
don't want to migrate those too.

DNS propagation can take anywhere from a few minutes to ~24 hours
(depends on your previous DNS's TTL). `dig yourdomain.com` or
`nslookup yourdomain.com` from a terminal shows whether it's resolved to
the hosting IP yet.

## Once it resolves: SSL and the WordPress site URL

1. In the hosting panel, add the domain (if not done automatically) and run
   **AutoSSL** (or the host's equivalent) to issue a free Let's Encrypt
   certificate. Do this *before* finishing the WordPress install so the
   site address can be set to `https://` from the start — changing a live
   site's protocol after the fact means also updating `siteurl`/`home` in
   the database (`wp-admin` → Settings → General, or directly via SQL if
   the admin screen is unreachable).
2. Force HTTPS: most panels have a "Force HTTPS Redirect" toggle per domain
   (SSL/TLS Status page) — turn it on rather than hand-writing `.htaccess`
   redirect rules.

Next: `wp-initial-setup.md` to actually get WordPress running at that
domain.
