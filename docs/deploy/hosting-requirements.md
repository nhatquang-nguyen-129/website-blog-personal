# Hosting and server requirements

## What kind of hosting

**Shared "SSD hosting" (cPanel), not a VPS.** This matches the reasoning
already in the project's `README.md`: the site doesn't have traffic yet, so
paying for and managing a VPS is premature. Shared SSD hosting from a
mainstream provider gives you:

- A control panel (cPanel is by far the most common — this doc assumes
  cPanel; DirectAdmin/Plesk equivalents exist for every step, just under
  different menu names)
- A **Softaculous** or similar one-click WordPress installer (optional —
  see `initial-deploy.md` for why a *manual* core install is actually
  simpler here, since Softaculous likes to manage `wp-config.php` and the
  DB itself in ways that fight with this project's own conventions)
- A MySQL database included
- Usually **SSH access** even on shared plans these days — check this
  specifically when buying, since it determines which sync method in
  `github-ci-cd.md` you can use. Not a dealbreaker if it's missing (there's
  an FTP-only path too), but nicer to have.
- Free SSL (AutoSSL / Let's Encrypt) included — practically universal now,
  confirm before buying anyway.

Any reasonable Vietnamese or international shared-hosting provider works —
there's nothing WordPress-specific to shop for beyond the requirements
below.

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
