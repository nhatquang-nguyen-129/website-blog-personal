# Changelog

All notable changes to this project are documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and versioning follows [Semantic Versioning](https://semver.org/) (`MAJOR.MINOR.PATCH`).

## How this file is maintained

- Day-to-day commits/pushes to a feature branch (e.g. `branch_1x`) do **not** need a changelog entry or a version bump — just add your change under `[Unreleased]` below.
- A version is only cut when a Pull Request is merged into `main`:
  1. Rename `[Unreleased]` to the new version + today's date, e.g. `## [1.1.0] - 2026-08-26`.
  2. Add a fresh empty `[Unreleased]` section above it.
  3. Tag the merge commit on `main`: `git tag v1.1.0 && git push origin v1.1.0`.
  4. Optionally turn the tag into a GitHub Release (`gh release create v1.1.0 --notes-from-tag` or via the GitHub UI) so it shows up under the repo's "Releases" tab.
- Version bump guide: **PATCH** (1.0.x) for fixes/tweaks, **MINOR** (1.x.0) for new features that don't break anything existing, **MAJOR** (x.0.0) for breaking changes (e.g. a change that requires re-running the WordPress installer or breaks an existing mu-plugin's data).

## [Unreleased]

### Added
- Docker-based local dev environment: `docker/docker-compose.yml` running WordPress (Apache + PHP 8.2) and MySQL 8.0 as separate, networked services.
- `docker/wp-config.docker.php` — wp-config template that reads DB credentials from environment variables instead of being hand-edited.
- `multilingual-post` MU-plugin: per-post language metabox, translation grouping via post meta, an admin UI to spin off a new-language draft from an existing post, and a `[language_switcher]` shortcode for the frontend.

### Fixed
- Dockerfile was missing the `mysqli`/`pdo_mysql`/`gd`/`zip` PHP extensions required by WordPress — added their install step.
