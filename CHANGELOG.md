# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog] and this project adheres to [Semantic Versioning][semver].

## [Unreleased]

## v6.1.0

### Added

- Add to `Str`: `padBoth`, `padLeft`, `padRight` (character counts, not bytes), `wrap`, `title`, `uuid` (RFC 4122 v4) and `ulid` (sortable by creation time)
- Add to `Arr`: `sortRecursive`, `divide`, `crossJoin`, `shuffle` (uses the CSPRNG) and `whereNotNull`
- Add to `ArrayCollection`: `median`, `flip`, `zip`, `every`, `some`, `countBy` and `lazy`, which returns a `Generator`
- Add to `HashCollection`: `sortBy`, `partition` and `groupBy`, bringing it level with `ArrayCollection`
- Add to `Number`: `format`, `clamp`, `percentage`, `humanize` and `ordinal`
- Add to `Bit`: `toggleFlag`, `flags`, `hasAll` and `hasAny`
- Add to `Storage`: `merge`, `only` and `except`
- Add `Json::prettyPrint` and `WithEnhances::random`
- Add `homepage` and `support` links to composer.json, and a `normalize-check` script
- Add a CI leg without `ext-intl` so the bundled-map fallback is exercised, and a downloadable coverage artifact

### Changed

- `Str::replaceByTemplate` returns `string` rather than `string|string[]`; a string input always yields a string
- `Func::dataGet` reports a public property holding `null` as present, returning `null` instead of the default
- `ConditionalHandler` no longer types its callbacks as `Closure(mixed ...)`, which rejected every concretely-typed closure
- `Arr::toPostgresPoint` is annotated as accepting floats, which it always did
- `composer test` also analyses `tests/`, through `phpstan-tests.neon`

### Fixed

- Fix `Arr::dataToArray` returning an empty array for a plain object, which made `Json::encode(new stdClass(...))` produce `"[]"`
- Fix `Bit::flags` never returning for large masks: the probe overflowed to zero and looped forever

## v6.0.0

See [UPGRADE.md](./UPGRADE.md) for migration instructions.

### Added

- Add `Php\Support\Func`, a static facade holding every global function's implementation, so the helpers can be called through a name no other package can shadow
- Add `Bit::hasFlagIn`, replacing `Bit::exist`
- Add `B64::decodeOrThrow`
- Add `UPGRADE.md`

### Changed

- Require PHP `^8.5`
- `Str::slugify` collapses repeated separators and trims them off both ends
- `Str::toSnake`, `toKebab`, `toDelimited`, `toScreamingSnake` and `toScreamingDelimited` detect word boundaries in any script, not only ASCII
- `Str::toCamel` and `toLowerCamel` keep non-ASCII letters instead of dropping them
- `Number::safeInt` returns non-integer values unchanged as strings instead of truncating them
- `B64::encodeSafe` uses the RFC 4648 URL-safe alphabet and strips padding; `decodeSafe` still reads the legacy `~` form
- `B64::decode` and `decodeSafe` are strict by default and return `null` for invalid input
- `Bit::decBinPad` rejects negative values
- `URLify::downcode` transliterates through ICU when `ext-intl` is installed, falling back to the bundled maps
- `WithEnhancesForStrings::casesToString` takes `(?callable $decorator, string $delimiter)`, matching `WithEnhances`
- `Point::castFromDatabase` is static
- `HashCollection::offsetSet` throws `InvalidParamException` instead of a bare `TypeError` when appending a non-object
- `ReadOnlyProperties` exposes only the properties named by `readOnlyProperties()`, and nothing by default
- `AdditionalAssertionsTrait::assertClassUsesTraits` uses the package's own trait helpers instead of reimplementing them
- PHPStan runs at level 7; `Arr`'s generic parameters moved from the class to the methods that can bind them
- `phpunit/phpunit` and `ext-intl` are declared in `suggest`

### Removed

- Remove `remoteStaticCallOrTrow`, the misspelled alias deprecated in 5.2.0
- Remove `Interfaces\Prototype` and `Interfaces\Command`
- Remove `UseErrorsBox::setError`, deprecated in 5.3.0
- Remove `Str::seemsUTF8`, a one-line proxy to `URLify::seemsUTF8`
- Remove `Bit::exist` in favour of `hasFlagIn`
- Remove `URLify::seemsUTF8Regex`, unreachable because `ext-mbstring` is required

### Fixed

- Fix `Str::toCamel` silently discarding every character outside the ASCII ranges

## v5.3.0

### Added

- Add `Exceptions\ExceptionInterface`, implemented by every exception of the package, so all of them can be caught in one block regardless of which SPL exception they extend
- Add `Json::decodeOrThrow`, `Json::encodeOrThrow` and `Json::isValid`
- Add to `Storage`: `IteratorAggregate` and `Arrayable` support, `all`, `toArray`, `isEmpty`, `clear`, `countRecursive`, and a `$separator` argument on `set`, `get` and `exist`
- Add to `HashCollection`: `IteratorAggregate` support, `toArray`, `keys`, `values`, `map`, `filter`, `each`, `reduce`, `implode` and the `createFrom` extension hook
- Add to `Arr`: `only`, `except`, `pluck`, `first`, `last`, `flatten`, `wrap`, `dot`, `undot`, `keyBy`, `where`, `isAssoc`, `isList`, and a `$strict` argument on `removeByValue`
- Add to `Str`: `random`, `mask`, `limit`, `contains`, `startsWith`, `endsWith`, `squish`, `after`, `before`, `between`, `ucFirst`, `lcFirst`, `clearCache` and the `CACHE_LIMIT` constant
- Add to `ArrayCollection`: `sum`, `avg`, `min`, `max`, `pluck`, `unique`, `keyBy`, `flatten`, `values`, `implode`, `tap`, `pipe`, `when`, `unless`, `diff`, `intersect`, `take`, `skip`, `toJson`
- Add `WithEnhances`: `hasValue` for int-backed enums, `tryFromName`, `fromName` and `labels`
- Add `UseErrorsBox`: `addError`, `firstError`, `error` and `errorsCount`
- Add `.editorconfig`, `CONTRIBUTING.md`, `SECURITY.md` and issue/PR templates

### Changed

- `Thrower::throw` returns `never`, `Maker::make` and `Singleton::getInstance` return `static`
- `Str` conversion caches are bounded at `CACHE_LIMIT` entries instead of growing without limit
- PHPStan runs at level 6 and no longer excludes `ArrayCollection`, `Point` and `GeoPoint`
- `@psalm-*` annotations converted to `@phpstan-*`
- Upgrade to PHPUnit 13

### Deprecated

- Deprecate `UseErrorsBox::setError`, which appends rather than replaces. Use `addError`

### Fixed

- Fix `URLify::downcode` leaking a language map into later calls: one call with an explicit language gave that map priority for the rest of the process, including calls that asked for no language
- Fix `Testing\TestingHelper::runProtectedMethod` and `::getProperty` raising a `TypeError` when given a class name, although both are documented as accepting `string|object`
- Fix the `@param` annotations of `ArrayCollection::sortBy` and `::sortByMany`, unparseable since the line wrapping in v5.2.1
- Fix `WithEnhances` being annotated `@mixin \UnitEnum` while reading `$case->value`
- Fix `WithEnhances::hasName` naming its parameter `$value`
- Fix `Point::castFromDatabase` declaring `?self` while returning `static`
- Fix `TraitInitializer::initializeTraits` reading an array key without a guard

## v5.2.1

### Fixed

- Fix `Arr::toPostgresArray` producing a broken array literal: an element containing a comma was split
  into several elements, quotes and backslashes leaked from the JSON escaping, and a string equal to
  `null` was written unquoted, so PostgreSQL read it back as SQL NULL
- Fix `Arr::fromPostgresArray` losing a quoted empty string and not unescaping backslash sequences
- Fix `Arr::fromPostgresPoint` turning any malformed value into the point `(0, 0)` with a warning
- Fix `ArrayCollection` and `HashCollection` not implementing `JsonSerializable`, so `json_encode()`
  on a collection silently returned `{}`
- Fix `UseConfigurableStorage` never calling `configureProps()`: an unknown key threw instead of
  being written to the `Storage`
- Fix `UseStorage` treating a declared but uninitialized typed property as present, which made
  reading it a fatal error instead of a storage lookup
- Fix `Arr::fillKeysByValues` returning all-null values for an associative `$keys` array
- Fix `Arr::merge` overwriting instead of appending when an integer key already held `null`
- Fix `Point::fromJson` and `GeoPoint::fromJson` raising warnings and a `TypeError` on a payload
  without the expected keys
- Fix `GeoPoint::toJson` declaring `string` while `Json::encode` may return `null`
- Fix `HasPrePostActions::getCallbackActions('0')` returning every group
- Fix `ConsolePrint` using the CLI-only `STDOUT`/`STDERR` constants
- Fix `Str::truncate` accepting a non-positive length
- Fix `Str::slugifyWithFormat` interpolating `$format` into the pattern unescaped
- Fix `Bit::toInt` silently converting a non-binary string to `0`
- Fix `ArrayCollection::getProperty` ignoring `$throwOnMiss` for arrays
- Fix `ArrayCollection::random` bypassing `createFrom()`, which broke subclasses
- Fix `MissingConfigException::$needKey` being stored but never used
- Fix deprecated `ReflectionMethod::setAccessible()` calls in `Testing\TestingHelper`
- Fix the release workflow not matching patch tags, and CI not running on pull requests

### Changed

- `Arr::toPostgresArray` quotes an element only when the array literal syntax requires it, and writes
  PHP `null` as the `NULL` keyword
- `Arr::set` returns the whole array instead of the innermost sub-array
- `Arr::remove` and `Storage::remove` accept a `$separator` argument, like `get`, `set` and `has`
- `Arr::random` throws `Php\Support\Exceptions\InvalidArgumentException`, a subclass of the global one
- `ArrayCollection::chunk` throws `InvalidParamException` for a non-positive size instead of returning
  an empty collection
- `ArrayCollection::mapByKey` throws `MissingPropertyException` for a missing key or property
- `Str::truncate`, `Str::slugifyWithFormat` and `Bit::toInt` throw `InvalidParamException` on invalid input
- `UseConfigurableStorage::configurable` writes unknown keys to the `Storage` instead of throwing
- PHPUnit now fails on warnings, deprecations, notices and risky tests; deprecations triggered by
  dependencies rather than by this package are excluded via `ignoreIndirectDeprecations`

### Added

- Add `ext-json` and `ext-ctype` to the package requirements
- Add `Arrayable` and `JsonSerializable` to `ArrayCollection` and `HashCollection`, plus
  `HashCollection::toArray`
- Add a PHPCS job to CI and a dependabot configuration

### Removed

- Remove the unused `candidates/` directory and `infection.json`
- Remove the unused `symfony/var-dumper` dev dependency and the `infection` composer script

## v5.2.0

### Added

- Add `Number::MAX_SAFE_INTEGER` constant
- Add global function `remoteStaticCallOrThrow` (now properly defined) with a deprecated `remoteStaticCallOrTrow` alias for backward compatibility

### Fixed

- Fix `remoteStaticCallOrThrow` was never declared because of a wrong `function_exists` guard
- Fix `Arr::removeByValue` losing valid falsy keys (`0`, `''`, `'0'`)
- Fix `Bit::decBinPad` integer overflow on large numbers; `Bit::checkFlag` now uses `!== 0`
- Fix `Number::safeInt` to keep large integer-like strings as string and compare strictly against `MAX_SAFE_INTEGER`; `Number::isInteger` no longer warns on non-scalars
- Fix `ArrayCollection::sortBy` returning an empty collection for a string field name
- Fix `ArrayCollection::getProperty` `UnhandledMatchError` on unsupported target types
- Fix `Str` camel/delimited cache collisions and make `toLowerCamel` multibyte-safe
- Fix `Json::encode` to return `null` only on `false`
- Fix `UseErrorsBox::setError` to read message from any `\Throwable`
- Fix `Whener::when` to use `??` instead of `?:`

### Removed

- Remove `ErrorCollection`
- Remove `UseSetter` trait

## v5.1.3

### Added

- Add lazy initialization of `UseStorage::$storage` via PHP 8.4 property hook
- Add `TO` generic template to `HashCollection`

### Fixed

- Fix implicitly nullable parameter types for `ArrayCollection::filter` and `HasPrePostActions::getCallbackActions` (PHP 8.4 deprecation)

## v5.1.2

### Changed

- Maintenance release (no code changes)

## v5.1.1

### Fixed

- Fix `instance` global function (add generic type hints)
- Fix `value` global function parameter naming

## v5.1.0

### Added

- Add `HashCollection` Collection

## v5.0.0

### Added

- Add `PHP 8.4` support
- Add `UseStorage` trait
- Add `UseConfigurabeStorage` trait

### Removed

- Remove Trait `ArrayStorage`. Use `UseStorage` instead
- Remove Trait `ArrayStorageConfigurableTrait`. Use `UseConfigurabeStorage` instead

## v4.28.0

### Added

- Add global functions: `attributeToGetterMethod`, `attributeToSetterMethod`, `findGetterMethod`, `public_property_exists`, `get_property_value`

## v4.27.0

### Added

- Add func for Enum's traits: `WithEnhances::toKeyValueArray`, `WithEnhances::toValueKeyArray`
- Add `Arr::map`

## v4.26.0

### Added

- Add support `PHP 8.3`

## v4.25.0

### Added

- `Arr::random` - Get one or a specified number of random values from an array
- `ArrayCollection::random` - Get one or a specified number of items randomly from the collection
- `ArrayCollection::clone` - Clone elements and returns Collection
- `ArrayCollection::groupBy` - Group an associative array by a field or using a callback

### Changed

- `ArrayCollection::map` - works with keys now
- `ArrayCollection::createFrom` - receives Collections

## v4.24.0

### Added

- Add an argument `separator` to methods `Arr::set`, `Arr::get`, `Arr::has`

## v4.23.0

### Added

- Add methods `trimPrefix`, `trimSuffix` into `Str`

## v4.22.0

### Added

- Add method `mapInto` into `ArrayCollection`

## v4.21.0

### Added

- Add method `whereInstanceOf` into `ArrayCollection`

## v4.20.0

### Added

- Add method `slugifyWithFormat` into `Str`

## v4.19.0

### Added

- Add traits for
  Enums: [WithEnhances.php](./src/Enums/WithEnhances.php), [WithEnhancesForStrings](./src/Enums/WithEnhancesForStrings.php)
  with following methods:
  - `casesToString`- Returns string of Enum's names or values
  - `casesToEscapeString`- Returns string of Enum's escaped names or values
  - `values`- Returns list of Enum's values
  - `names` - Returns list of Enum's names
  - `hasValue` - Check if the Enum has provided Value
  - `hasName` - Check if the Enum has provided Name

## v4.18.0

### Added

- Add function `Collection::reject`

## v4.17.1

### Changed

- `Collection::filter(Closure $func = null)` - The argument `$func` may be `null`

## v4.17.0

### Added

- Add support `PHP 8.2`

### Removed

- Remove support `PHP 8.0`

## v4.16.0

### Added

- Add global method `dataGet`
- Add helper method `Arr::collapse`
- Add helper method `Arr::prepend`
- Add Structures: `ArrayCollection` and its interfaces

## v4.15.0

### Added

- Add global method `mapValue` Returns an array containing the results of applying func to the items of the $collection
- Add global method `eachValue` Apply a $fn to all the items of the $collection

## v4.14.0

### Added

- Add method `Number::isInteger` Allows you to determine whether the $value is an integer or not

## v4.13.0

### Added

- Add support `PHP 8.1`

## v4.9.0

### Added

- Add method `Str::truncate`: truncate a string to a specified length without cutting a word off
- Add method `Str::slugify`: generate a string safe for use in URLs from any given string
- Add method `Str::seemsUTF8`: checks to see if a string is utf8 encoded
- Add method `Str::removeAccents`: converts all accent characters to ASCII characters
- Add method `URLify::downcode`: transliterates characters to their ASCII equivalents

## v4.8.0

### Added

- Add methods `toPostgresPoint`, `fromPostgresPoint` to `Arr` helper

## v4.7.0

### Added

- Add exception `MissingMethodException`
- Add global function `remoteStaticCallOrTrow`

## v4.6.0

### Added

- Add class `ConditionalHandler`

## v4.5.0

### Added

- Add trait `HasPrePostActions`

## v4.4.2

### Changed

- Add param `removeNull` to method: `Metable::setMetaAttribute`

## v4.4.0

### Added

- Add global function: `does_trait_use`

## v4.3.1

### Added

- Add global function: `remoteCall`
- Add global function: `remoteStaticCall`

## v4.2.0

### Added

- Add method to trait `Metable`: `setMetaAttribute`

## v4.1.0

### Added

- Add new Helper Class: `Number`
- Add method, working with integers: `Number::safeInt`

## v4.0.0

### Changed

- The package has PHP's minimal version is 8.0 now

[keepachangelog]:https://keepachangelog.com/en/1.0.0/

[semver]:https://semver.org/spec/v2.0.0.html

[Unreleased]: https://github.com/efureev/php-support/compare/v6.1.0...HEAD

[6.1.0]: https://github.com/efureev/php-support/compare/v6.0.0...v6.1.0

[6.0.0]: https://github.com/efureev/php-support/compare/v5.3.0...v6.0.0

[5.3.0]: https://github.com/efureev/php-support/compare/v5.2.1...v5.3.0

[5.2.1]: https://github.com/efureev/php-support/compare/v5.2.0...v5.2.1

[5.2.0]: https://github.com/efureev/php-support/compare/v5.1.3...v5.2.0
