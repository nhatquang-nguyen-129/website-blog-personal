
| Branch | Purpose |
|------|--------|
| `main` | **Stable branch** – ready for deployment |
| `development` | Active development & feature work |
| `experiment/*` | Isolated experiments, prototypes, or architectural tests |

Only **code and deterministic artifacts** are promoted to `main`.

## Versioning

Feature branches (e.g. `branch_1x`) are pushed freely without bumping any version. A version is only cut when a Pull Request merges into `main` — see [CHANGELOG.md](./CHANGELOG.md) for the exact steps and the running log of what shipped in each version.

---

## Repository Scope

This repository intentionally includes **only what should be version-controlled**.

### ✅ Included
- Custom themes (e.g. GeneratePress child theme)
- Custom plugins
- Frontend assets (JS, CSS, build configs)
- Schema definitions (CPTs, taxonomies, block structures)
- Seed scripts & demo content
- Documentation

### ❌ Excluded
- WordPress core files
- Environment-specific configuration (`wp-config.php`)
- Database dumps
- Media uploads
- Secrets & credentials

See `.gitignore` for details.

---

## Content Strategy

- **Production content** lives in the database and is **not versioned**
- **Demo / test content** is generated via:
  - Seed scripts
  - Importable markdown or XML files
- This allows consistent UI & feature testing across multiple environments without coupling logic to real content

The long-term goal is to support **file-based content workflows** (e.g. Markdown → WordPress import), enabling content portability and multi-device authoring.

---

## Customization Principles

This blog prioritizes:

- Minimal themes (layout over decoration)
- Clear typography & reading experience
- Dark mode support
- Multilingual posts (language switching within the same article)
- Progressive enhancement over heavy plugins

Design inspiration leans toward **writing-first platforms** (e.g. Substack-style layouts), rather than traditional WordPress blog aesthetics.

---

## Local Development

Typical local workflow:

```bash
# Start PHP built-in server
php -S localhost:8000 -t public

# Access admin
http://localhost:8000/wp-admin