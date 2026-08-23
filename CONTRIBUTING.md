# Contributing

Thanks for taking the time to contribute.

## Getting started

```bash
composer install
composer test          # phpstan + phpunit
```

## Before opening a pull request

The pipeline runs these three, and so should you:

```bash
composer phpstan          # static analysis of src/, level 7
composer phpstan-tests    # static analysis of tests/
composer phpunit          # test suite
composer phpcs            # PSR-12 + the project ruleset
composer normalize-check  # composer.json formatting
composer cs-fix           # fixes most style violations automatically
```

The test suite is strict on purpose: it fails on warnings, deprecations, notices
and risky tests. A change that introduces any of those will not pass CI.

Deprecations raised by dependencies rather than by this package are excluded, so
if you see one from `vendor/`, it is not yours to fix.

## Expectations for a change

- **Every bug fix comes with a test that fails without it.** The audit that
  produced most of the current test suite found several defects precisely
  because a test was written for previously untested code.
- **Do not grow `phpstan-baseline.neon` or `phpstan-tests-baseline.neon`.** They hold
  known limits of what generics can express, and findings in fixtures that are not worth
  chasing. Both are meant to shrink, not grow.
- **Public API changes go in `CHANGELOG.md`**, under `Added`, `Changed`,
  `Deprecated`, `Removed`, `Fixed` or `Security` - the changelog linter accepts
  nothing else, and list entries must not end with punctuation.
- **Behaviour changes belong under `Changed`**, not `Fixed`, even when the old
  behaviour was a bug. Someone is relying on it.

## Versioning

The package follows semantic versioning. Breaking changes wait for the next
major; see the release plan in `AUDIT.md` for what is already queued for 6.0.
