# Security Policy

## Supported versions

| Version | PHP       | Supported          |
|---------|-----------|--------------------|
| 5.x     | 8.4, 8.5  | :white_check_mark: |
| 4.x     | 8.1 - 8.3 | security fixes     |
| <= 3.x  | <= 8.0    | :x:                |

## Reporting a vulnerability

Please report security issues privately through
[GitHub Security Advisories](https://github.com/efureev/php-support/security/advisories/new)
rather than opening a public issue.

Include what you can: affected version, a minimal reproduction, and the impact
you believe it has. You can expect an initial response within a few days.

## Scope

This is a utility library with no network, filesystem or database access of its
own. The parts most likely to matter for security are the ones that take
untrusted input and produce output for another system:

- `Arr::toPostgresArray()` and `Arr::toPostgresPoint()` build PostgreSQL
  literals
- `Str::slugify()` and `Str::slugifyWithFormat()` build URL fragments, and the
  latter compiles a caller-supplied regular expression
- `Json::encode()` / `decode()` and the `B64` helpers
