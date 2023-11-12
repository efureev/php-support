# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog][keepachangelog] and this project adheres to [Semantic Versioning][semver].

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
