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
