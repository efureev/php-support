# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog] and this project adheres to [Semantic Versioning][semver].

## v4.1.0

### Added

- Add new Helper Class: `Nubmer`.
- Add method, working with integers: `Number::safeInt`.

## v4.0.0

### Changed

- The package has PHP's minimal version is 8.0 now.

## v3.1.1

### Changed

- Remove default option `JSON_THROW_ON_ERROR` from `JSON::encode` & `JSON::decode`

## v3.1.0

### Changed

- Changed behavior on `JSON::encode` & `JSON::decode`: add default option `JSON_THROW_ON_ERROR`. Remove
  custom `JsonException`

## v3.0.0

### Added

- Add support PHP: minimal >= 7.4 and maximum <=8.0

## v2.14.0

### Added

- Add new global function `class_basename`. Get the class "basename" of the given object / class.
- Add new global function `trait_uses_recursive`. Returns all traits used by a trait and its traits.
- Add new global function `class_uses_recursive`. Returns all traits used by a class, its parent classes and trait of
  their traits.
- Add trait `TraitBooter`. Helps to boot trait's static `boot-function`.
- Add trait `TraitInitializer`. Helps to init trait's `initialize-function`.

## v2.13.0

### Added

- Add new global function `instance`

## v2.12.0

### Added

- Add new trait `ReadOnlyProperties`

## v2.11.6

### Added

- Add into trait `Thrower` new function `throwIf`

## v2.11.3

### Added

- Add into `Str` helper function for define Regular Expression `isRegExp`

## v2.11.2

### Added

- Add into `Arr` helper function for fill a keyed array by values from another array: `fillKeysByValues`

## v2.11.1

### Added

- Add into `Arr` helper function for finding duplicates: `duplicates`

## v2.11.0

### Added

- Add trait `Whener`
- Add trait `Thrower`

## v2.10.0

### Added

- Add global function: `when`

## v2.9.0

### Added

- Add global function: `isTrue`

## v2.8.0

### Added

- Add Helper for base64: `B64`

## v2.7.0

### Added

- Add types `Point` and `GeoPoint`

## v2.6.3

### Added

- Helper `Bit`: function `decBinPad`. Convert decimal to binary string with left pad zero-filling

## v2.6.0

### Added

- Helper `Bit`: contains operations with bits and bit-masks

## v2.5.0

### Changed

- Logic has been changed for the trait `ConfigurableTrait::configurable`:
  at first, on applying props, checking a magic method and then a property

## v2.4.2

### Changed

- Fix the trait `ConfigurableTrait::configurable`

## v2.4.0

### Added

- Add global function `classNamespace`

## v2.3.0

### Added

- Move Helper::Arr::ToPostgresArray from candidate to basic functionality

## v2.2.5

### Added

- Add functionality to trait `ArrayStorage`: now it implements `Arrayable`

## v2.2.4

### Added

- Add functionality to trait `ArrayStorage`: now it implements `ArrayAccess`

## v2.2.3

### Added

- Add trait `ArrayStorageConfigurableTrait`

## v2.2.2

### Added

- Fix CI

## v2.2.0

### Added

- Add function `has` to `Array` Helper
- Add function `set` to `Array` Helper
- Add function `remove` to `Array` Helper

## v2.1.1

### Added

- Add trait Maker

## v2.1.0

### Added

- Add trait Metable

## v2.0.5

### Added

- Add new String helpers:
    + `replaceByTemplate` Replace templates into string
- Add new Array helpers:
    + `replaceByTemplate` Replace templates into array

## v2.0.2

### Added

- Add new String helpers:
    + `replaceStrTo` Replace substr by start and finish indents

## v2.0.1

### Added

- Add `CHANGELOG.md`
- Add new Array helpers:
    + `get` Get an item from an array using "dot" notation
- Add new String helpers:
    + `toSnake` Converts a string to `snake_case`
    + `toScreamingSnake` Converts a string to `SCREAMING_SNAKE_CASE`
    + `toKebab` Converts a string to `kebab-case`
    + `toCamel` Converts a string to `CamelCase`
    + `toLowerCamel` Converts a string to `lowerCamelCase`
    + `removeMultiSpace` Converts all multi-spaced characters to once

## v2.0.0

### Reformat

[keepachangelog]:https://keepachangelog.com/en/1.0.0/

[semver]:https://semver.org/spec/v2.0.0.html
