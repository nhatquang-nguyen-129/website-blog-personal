# Hosting and server requirements

## What kind of hosting

**Shared "SSD hosting" (cPanel), not a VPS.** This matches the reasoning
already in the project's `README.md`: the site doesn't have traffic yet, so
paying for and managing a VPS is premature. Shared SSD hosting from a
mainstream provider gives you:

- A control panel (cPanel is by far the most common — this doc assumes
  cPanel; DirectAdmin/Plesk/1Panel equivalents exist for every step, just
  under different menu names. This project's actual iNET plan turned out to
  run on **1Panel** ("OnePanel") — see
  [`onepanel-setup.md`](./onepanel-setup.md) for the concrete menu mapping
  and one real gotcha)
- A **Softaculous** or similar one-click WordPress installer (optional —
  see `wp-initial-setup.md` for why a *manual* core install is actually
  simpler here, since Softaculous likes to manage `wp-config.php` and the
  DB itself in ways that fight with this project's own conventions)
- A MySQL database included
- Usually **SSH access** even on shared plans these days — check this
  specifically when buying, since it determines which sync method in
  `github-workflows.md` you can use. Not a dealbreaker if it's missing (there's
  an FTP-only path too), but nicer to have.
- Free SSL (AutoSSL / Let's Encrypt) included — practically universal now,
  confirm before buying anyway.

Any reasonable Vietnamese or international shared-hosting provider works —
there's nothing WordPress-specific to shop for beyond the requirements
below.

## Worked example: checkout at a Vietnamese host (iNET)

This project's actual domain + hosting was bought at iNET. Their checkout
flow bundles a domain purchase with a "Gợi ý dịch vụ" (suggested add-ons)
screen — worth documenting how each one maps to what's actually needed,
since the same pattern (a bundle of upsells alongside the thing you
actually came for) is typical of most Vietnamese hosting checkouts, not
just this one:

| Suggested add-on | Buy it? | Why |
|---|---|---|
| **Hosting** (NVMe SSD, cPanel-style) | **Yes** | This is the "shared SSD hosting" this doc recommends — the actual thing needed. |
| **OneShield** (free: hides origin IP, WAF, traffic analytics) | **Yes, keep it on** | Free, and a real security improvement (hiding the origin IP alone blunts a lot of direct-IP attacks) — no reason to decline something free that only adds protection. |
| **SSL** (paid, offered separately) | **No** | Confirmed the chosen hosting tier already bundles free SSL (see below) — a separately-sold cert would be pure duplication. A personal blog also has no need for the paid tiers' higher CA warranty or wildcard support. |
| **Email** (a premium webmail product, e.g. "OneMail") | **No** | Shared hosting almost always includes basic cPanel email accounts (`admin@yourdomain.com`, etc.) at no extra cost — enough for a personal blog. A separate paid email product is a nicer webmail UI/extra storage, not a requirement. |
| **Cloud Server** | **No** | This is a VPS/cloud-compute product under another name — exactly what "What kind of hosting" above says to skip for now. Don't let it get bundled in just because it's shown on the same screen as the domain/hosting purchase. |

### Which hosting tier

iNET's WordPress-specific line (as opposed to generic shared hosting) comes
in three tiers — WP-H1/H2/H3 — that differ **only** in how many websites
you're allowed and how much CPU/RAM/storage you get. Every security and
platform feature (WAF, malware scan + 2FA, Website Isolation, 1-click
WordPress install, daily backups, LiteSpeed + LiteSpeed Cache, free SSL) is
**identical across all three tiers** — paying more buys capacity, not
capability.

**WP-H1** is the right one for this project: 1 website is all that's
needed, and its 5 GB SSD / 2-core / 2 GB RAM is plenty for a text-first
personal blog with no meaningful traffic yet. Its included feature list
covers two things this doc would otherwise have called out separately:

- **"Miễn phí SSL (ZeroSSL)"** — confirms the free-SSL assumption above.
  Skip every paid SSL product on the checkout entirely.
- **"Backup dữ liệu hàng ngày"** — automated daily backups from the host
  itself, covering the DB and `wp-content/uploads/` that this repo
  deliberately doesn't track in git (see `README.md`'s Content Strategy).
  Worth confirming in the panel after signup (retention window, whether it
  covers files *and* DB) rather than assuming, but it means this project
  likely doesn't need a separate backup plugin/service on top.

Two "Quà tặng" (bonus gifts) come with it — a MyThemeShop theme bundle and
Rank Math SEO Pro. The theme bundle isn't relevant (this project has its
own theme, `minimal-reader` — see `docs/themes/minimal-reader.md`). Rank
Math SEO Pro is worth considering separately later: WordPress core has no
built-in SEO/meta-tag editing, so unlike most things in this project's
`docs/plugins/`, that's a real gap a plugin genuinely fills rather than
something to build custom — but it's an optional bonus to activate when
wanted, not a deploy requirement.

If it upgrades later (more sites, more traffic), the general lesson still
applies beyond just iNET: read every "suggested service" on a
domain/hosting checkout against what "What kind of hosting" and "Server
requirements" actually call for, rather than assuming everything
pre-selected or upsold on that screen is necessary. Promo codes shown at
checkout (a new-customer welcome code, a percentage off a specific product
line) are time-limited and provider-specific, so not documented here —
just worth checking for at time of purchase.

## Server requirements

Match what the `Dockerfile` already installs for local dev — if the host
doesn't meet these, ask their support to enable them (standard requests,
not unusual on a WordPress-oriented host):

| Requirement | Why | Where it's defined locally |
|---|---|---|
| PHP 8.2+ | Matches the Docker image (`php:8.2-apache`) | `Dockerfile` line 1 |
| `mysqli` + `pdo_mysql` extensions | WordPress's DB layer | `Dockerfile` "PHP extensions" step |
| `gd` extension | Image resizing (featured images, thumbnails) | same |
| `zip` extension | Plugin/theme uploads, WordPress core updates | same |
| MySQL 5.7+ / MariaDB 10.3+ | Matches the `mysql:8.0` Docker image closely enough | `docker/docker-compose.yml` |
| `mod_rewrite` (Apache) or equivalent (nginx `try_files`) | Pretty permalinks | `Dockerfile`'s `a2enmod rewrite` |

Virtually every mainstream WordPress-oriented shared host satisfies all of
this by default — this table is here so you know what to check for if
something doesn't work after deploying, not because it's likely to be an
issue.

## What you'll need from the hosting panel before moving on

Write these down — the next two docs use them:

- FTP/SFTP (or SSH) credentials and hostname
- MySQL database name, username, password, and host (usually `localhost`
  from the panel's point of view)
- The server's IP address (for the DNS step)
