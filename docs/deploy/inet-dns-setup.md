# iNET domain DNS setup

This project's domain and hosting were both bought at iNET, so DNS is
managed through iNET's own portal rather than a separate registrar or a
third-party DNS provider like Cloudflare. This is `dns-configuration.md`'s
**Option B** (manual A/CNAME records) in practice — iNET's official guide
doesn't use nameserver delegation at all, so there's no Option A here.

Source: [iNET's own guide](https://helpdesk.inet.vn/knowledgebase/huong-dan-tro-ten-mien-ve-hosting).

## Where to do it

1. Log in at https://portal.inet.vn/ with the account email.
2. Go to **"Tên miền"** (Domains) and select the domain.
3. Open **"Bản ghi"** (DNS records) for that domain.

## Records to create

| Type | Host | Value | Purpose |
|---|---|---|---|
| A | `@` | the hosting server's IP address (1Panel → Dashboard) | `yourdomain.com` → hosting |
| CNAME | `www` | `yourdomain.com` | `www.yourdomain.com` → same site |

Same two records `dns-configuration.md`'s Option B already describes — this
file only exists to record exactly where to enter them for this project's
specific registrar.

## Verify

- iNET's own guide checks with `ping yourdomain.com` from a terminal;
  `dig`/`nslookup` work the same way and are what `dns-configuration.md`
  already recommends.
- Compare the resolved IP against the hosting server's real IP.
- iNET states records take effect in **up to ~30 minutes** — faster than the
  "up to 24 hours" general estimate in `dns-configuration.md`, likely because
  domain and hosting share the same provider's DNS infrastructure.

## What iNET's guide doesn't cover

Nothing about SSL. Once the A record resolves, still go issue the free
ZeroSSL certificate through 1Panel yourself — see `dns-configuration.md`'s
"Once it resolves: SSL and the WordPress site URL" section and
`inet-onepanel-setup.md`. It isn't automatic just because domain and hosting are
at the same provider.

Next: `wp-initial-setup.md`.
