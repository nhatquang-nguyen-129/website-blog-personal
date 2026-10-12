# Release process

How this project's version number (`vX.Y.Z`) is decided and bumped, and the
one rule that gates every git action around it.

## Versioning scheme

- **`Z` (patch)** — minor change, bug fix. No new behavior, nothing an editor
  would notice except something that used to be broken now isn't.
- **`Y` (minor)** — a real feature update/addition. New capability, but
  nothing existing breaks.
- **`X` (major)** — a full overhaul of a major piece of the architecture.
  Something that changes how the project is built or run, not just what it
  can do.

This is the same PATCH/MINOR/MAJOR split `CHANGELOG.md` already uses — see
that file's "How this file is maintained" section for the actual mechanics
of cutting a version (renaming `[Unreleased]`, tagging the merge commit on
`main`, optionally turning the tag into a GitHub Release). This doc is the
policy; `CHANGELOG.md` is the log plus the step-by-step.

## Always ask before any git action

Before committing, pushing, merging, tagging, or making any other
git-related change — no matter how small the change looks — ask the user
two things first, rather than deciding either on your own:

1. **Keep the current version as-is**, or
2. **Bump it** — and if so, to what version, with the reasoning tied to the
   scheme above (which of X/Y/Z, and why).

This holds even for changes that feel obviously patch-level (a typo fix, a
one-line CSS tweak) — the version decision belongs to the user, not to an
inference from diff size. Don't bump `CHANGELOG.md`'s version header, tag a
commit, or merge `branch_1x` into `main` without that answer first.

## Shipping a cut version to production

The full path from a change on `branch_1x` to it being live, standardized
from how this project's actual releases (through v1.0.3) have gone:

1. **Finish the change(s) on `branch_1x`.** Day-to-day commits here don't
   need a changelog entry or version bump (`CHANGELOG.md`'s own rule) —
   only do steps 2+ once there's something worth shipping.
2. **Ask the user the two questions above** (keep vs. bump, and to what)
   before touching `CHANGELOG.md` or making any commit tied to the release.
3. **Cut the version in `CHANGELOG.md`**: rename `[Unreleased]` to
   `## [X.Y.Z] - <today's date>`, add a fresh empty `[Unreleased]` above
   it, write the actual entries for what changed under the new version
   heading.
4. **Commit on `branch_1x`**, then **push** (`git push origin branch_1x`)
   — both still git actions the always-ask rule covers.
5. **Open a PR, `branch_1x` → `main`, and merge it** — this project's
   convention has the user do this step themselves rather than Claude
   merging on their behalf, even once pushed.
6. **The merge into `main` triggers the real deploy automatically** — see
   `docs/deploy/github-workflows.md` for the mechanics (this project
   deploys over FTPS; SSH doesn't work on its current hosting). Nothing
   manual needed here if the five FTP secrets are already set correctly.
7. **Verify the deploy actually succeeded** — don't assume a merge means a
   successful release:
   ```bash
   gh run list --limit 3
   gh run view <run-id> --log-failed   # only if it shows "failure"
   ```
8. **Tag the merge commit on `main`** once confirmed live:
   `git tag vX.Y.Z && git push origin vX.Y.Z`. Optionally turn it into a
   GitHub Release (`gh release create vX.Y.Z --notes-from-tag`).

Steps 4-5 and 8 are git actions on `main`/tags specifically — same
always-ask rule, no exceptions for "it's basically done already."
