# AGENTS.md

This file documents **how to work in this repository** (for humans and coding agents).

## Repository overview

- **Workspace root**: `/workspace`
- **Current state**: Minimal skeleton repo (no source directories, build system, or tests configured yet).

## Development workflow (current)

- **Build**: Not configured.
- **Tests**: Not configured.
- **Lint/format**: Not configured.

If you add a language/runtime (Node/Python/Go/etc.), also add:

- A dependency/lock file appropriate for the ecosystem
- A minimal “how to run” section in the README
- At least one fast CI-friendly check (lint or unit tests)

## Conventions

- **Keep changes small and scoped**: Prefer incremental commits/PRs that do one thing well.
- **Prefer explicit structure**: If introducing code, create a conventional layout (e.g. `src/`, `tests/`, `docs/`) and document it.
- **Documentation**: Use Markdown. Keep instructions copy/paste friendly.

## Security and hygiene

- **Never commit secrets**: API keys, tokens, credentials, private certificates, `.env` files with real values.
- **Be deterministic**: Prefer pinned dependencies/lockfiles once a package manager is introduced.
- **Avoid repo-wide churn**: Don’t reformat or rename large swaths of files unless that’s the goal of the change.

## When adding automation

If you add tooling (lint, formatting, CI), ensure it is:

- **Fast** (runs locally in seconds where possible)
- **Reproducible** (document exact commands)
- **Minimal** (avoid heavy frameworks unless needed)

