# Contributing to MeetMe

Thanks for wanting to help! MeetMe is small, opinionated, and open source — contributions are
welcome as long as they fit the grain of the project.

## Ground rules

- **Conventions are enforced, not suggested.** [`CLAUDE.md`](CLAUDE.md) documents the coding
  conventions, and the Pest **architecture tests** (`tests/Unit/ArchTest.php`) enforce the
  Laravel and security presets. PRs that fight the arch tests will be asked to change, not the
  tests. Run `composer ci:check` before you push — CI runs the same gate.
- **Every change ships with a test.** New feature or bugfix, add or update a Pest test that
  proves it. CI won't go green otherwise, and neither will review.
- **One vertical slice per PR.** MeetMe is built one REST action at a time — migration, model,
  request, controller, page, and tests together. Keep PRs focused and small.
- **Run the formatter.** `composer lint` fixes Pint, Prettier, and docblock style in place.
  Docblocks carry types, not prose — the tooling strips summary comments.

## Getting set up

Follow the [README quick start](README.md#quick-start). `composer dev` runs the app, queue,
Vite, and SSR together; `php artisan migrate --seed` gives you a fully explorable app.

## Ideas and bugs

- **Ideas** → open an issue using the **Idea** template. Lightweight is fine; a sentence beats
  nothing.
- **Bugs** → open an issue with steps to reproduce.
- **Security** → do not open a public issue; see [SECURITY.md](SECURITY.md).

## Pull requests

1. Branch from `main` (`feat/short-description`).
2. Make the change with a test.
3. `composer ci:check` — green locally.
4. Open the PR describing what and why. CI (Pint, PHPStan, Pest on MySQL) must pass before merge.
