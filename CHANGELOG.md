# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog] and this project adheres to [Semantic Versioning][semver].
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
    + `get` Get an item from an array using "dot" notation.
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