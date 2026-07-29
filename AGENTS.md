# Sendex — agent / contributor notes

## Workflow

All changes go through a **Pull Request**. Do not commit or push directly to `master`.

1. Branch from up-to-date `master` (`fix/…`, `feat/…`, `chore/…`).
2. Commit on the branch, open a PR, wait for CI, merge.
3. Releases/tags after merge (or a version-bump PR, then tag).
4. Do not ship transport zips outside git + the release workflow.

Exception: only if the user explicitly asks to commit/push to `master` in that message.
