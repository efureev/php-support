# PHP Support

![](https://img.shields.io/badge/php-8.5-blue.svg)
![PHP Package](https://github.com/efureev/php-support/workflows/PHP%20Package/badge.svg?branch=master)
[![Latest Stable Version](https://poser.pugx.org/efureev/support/v/stable?format=flat)](https://packagist.org/packages/efureev/support)
[![Total Downloads](https://poser.pugx.org/efureev/support/downloads)](https://packagist.org/packages/efureev/support)

## Install

For php >= 8.5

```bash
composer require efureev/support "^6.0"
```

For php >= 8.4

```bash
composer require efureev/support "^5.3"
```

For php >= 8.1 (8.1, 8.2, 8.3)

```bash
composer require efureev/support "^4.19"
```

For php >= 7.4 and <=8.0

```bash
composer require efureev/support "^3.0"
```

For php >= 7.2 && <=7.4

```bash
composer require efureev/support "^2.0"
```

## Content

- Helpers
    + Array
      - collapse (^4.16.0)
      - prepend (^4.16.0)
      - accessible
      - dataToArray
      - duplicates
      - exists
      - fillKeysByValues
      - fromPostgresArray
      - fromPostgresArrayWithBraces
      - fromPostgresPoint (^4.8.0)
      - get
      - has
      - map (^4.27.0)
      - merge
      - random (^4.25.0)
      - remove
      - removeByValue
      - replaceByTemplate
      - set
      - toArray
      - toIndexedArray
      - toPostgresArray
      - toPostgresPoint (^4.8.0)
      - only (^5.3.0)
      - except (^5.3.0)
      - pluck (^5.3.0)
      - first (^5.3.0)
      - last (^5.3.0)
      - flatten (^5.3.0)
      - wrap (^5.3.0)
      - dot (^5.3.0)
      - undot (^5.3.0)
      - keyBy (^5.3.0)
      - where (^5.3.0)
      - isAssoc (^5.3.0)
      - isList (^5.3.0)
    + String
        - isRegExp
        - removeAccents (^4.9.0)
        - removeMultiSpace
        - replaceByTemplate
        - replaceStrTo
        - slugify (^4.9.0)
        - slugifyWithFormat
        - toCamel
        - toCamelInitCase
        - toDelimited
        - toKebab
        - toLowerCamel
        - toScreamingDelimited
        - toScreamingSnake
        - toSnake
        - trimPrefix
        - trimSuffix
        - truncate (^4.9.0)
        - after (^5.3.0)
        - before (^5.3.0)
        - between (^5.3.0)
        - clearCache (^5.3.0)
        - contains (^5.3.0)
        - endsWith (^5.3.0)
        - lcFirst (^5.3.0)
        - limit (^5.3.0)
        - mask (^5.3.0)
        - random (^5.3.0)
        - squish (^5.3.0)
        - startsWith (^5.3.0)
        - ucFirst (^5.3.0)
    + Json
        - decode
        - decodeOrThrow (^5.3.0)
        - encode
        - encodeOrThrow (^5.3.0)
        - htmlEncode
        - isValid (^5.3.0)
    + Bit
        - addFlag
        - checkFlag
        - decBinPad
        - hasFlagIn
        - grant
        - removeFlag
    + B64 (RFC 4648 URL-safe alphabet since ^6.0)
        - decode
        - decodeOrThrow (^6.0)
        - decodeSafe
        - encode
        - encodeSafe
    + Number
        - isInteger (^4.14.0)
        - safeInt (^4.1.0)

- Func (^6.0) - every global function below as a static method on `Php\Support\Func`,
  which no other package can shadow

- Global functions
    + attributeToGetterMethod (^4.28.0)
    + attributeToSetterMethod (^4.28.0)
    + classNamespace
    + class_basename
    + class_uses_recursive
    + dataGet (^4.16.0)
    + does_trait_use (^4.4.0)
    + eachValue (^4.15.0)
    + findGetterMethod (^4.28.0)
    + findSetterMethodByProp
    + getPropertyValue (^4.28.0)
    + instance
    + isTrue
    + mapValue (^4.15.0)
    + public_property_exists (^4.28.0)
    + remoteCall (^4.3.1)
    + remoteStaticCall (^4.3.1)
    + remoteStaticCallOrThrow (^4.7.0)
    + trait_uses_recursive
    + value
    + when

- Enums (^4.19.0)
    - casesToEscapeString
    - casesToString
    - hasName
    - hasValue
    - names
    - toKeyValueArray (^4.27.0)
    - toValueKeyArray (^4.27.0)
    - values
    - fromName (^5.3.0)
    - labels (^5.3.0)
    - tryFromName (^5.3.0)

- Exceptions
    + ExceptionInterface (^5.3.0) - marker implemented by all of them
    + ConfigException
    + Exception
    + InvalidArgumentException
    + InvalidCallException
    + InvalidConfigException
    + InvalidParamException
    + InvalidValueException
    + MethodNotAllowedException
    + MissingClassException
    + MissingConfigException
    + MissingPropertyException
    + MissingMethodException (^4.7.0)
    + NotSupportedException
    + UnknownMethodException
    + UnknownPropertyException

- Interfaces
    + Arrayable
    + Jsonable

- Structures
    - Collections (^4.16.0)
      - ArrayCollection - an ordered map; implements `Collection`, `Arrayable`, `JsonSerializable`,
        `IteratorAggregate`, `ArrayAccess`, `Countable`, `Stringable`
      - HashCollection (^5.1.0) - a string-keyed map; `add()` uses the element's class name as the key.
        Iterable, with `keys`, `values`, `map`, `filter`, `each`, `reduce`, `implode` (^5.3.0)

- ConditionalHandler
- Storage (^5.0.0)
    + set, get, remove, exist - dot notation, `$separator` configurable on all four (^5.3.0)
    + all, toArray, isEmpty, clear, countRecursive (^5.3.0)
    + count, jsonSerialize, `$data` (read-only)
    + also usable through property access, `ArrayAccess` and `foreach` (^5.3.0)

- Traits
    + UseStorage
    + UseConfigurableStorage
    + UseErrorsBox
    + ConfigurableTrait
    + ConsolePrint
    + HasPrePostActions
    + Maker
    + Metable
    + ReadOnlyProperties
    + Singleton
    + Thrower
    + TraitBooter
    + TraitInitializer
    + Whener

- Types
    + GeoPoint
    + Point

- Testing (traits for your own test suite; `AdditionalAssertionsTrait` needs phpunit/phpunit,
  declared in `suggest`)
    + AdditionalAssertionsTrait - `assertClassUsesTraits`
    + TestingHelper - `runProtectedMethod`, `getProperty`

- URLify
    + downcode - transliterates to ASCII through ICU when `ext-intl` is present (^6.0),
      through the bundled character maps otherwise
    + seemsUTF8

## Notes

Every exception this package throws implements `Php\Support\Exceptions\ExceptionInterface`, so a
consumer can catch all of them at once even though the concrete classes extend different SPL
exceptions:

```php
try {
    // ...
} catch (\Php\Support\Exceptions\ExceptionInterface $e) {
}
```


`Str::slugify()` and `Str::removeAccents()` transliterate through ICU when `ext-intl` is installed,
and fall back to a bundled character map otherwise. The map covers Latin, Cyrillic and Greek but
not the `Œ`/`œ` ligature or CJK, so installing `ext-intl` is recommended.

The global functions are all declared under `function_exists()`. If another package - Laravel
defines `value()`, `class_basename()` and `class_uses_recursive()` - got there first, its version
wins and this one is skipped, which makes behaviour depend on autoload order. Call
`Php\Support\Func::method()` where that matters.

`Arr::toPostgresArray` quotes an element only when the PostgreSQL array literal requires it, and
writes PHP `null` as the `NULL` keyword. `Arr::fromPostgresArray` reads unquoted `NULL` back as the
string `'NULL'`, so a round trip is not value-preserving for `null`.

## Test

```bash
composer test
composer test-cover # with coverage
```
