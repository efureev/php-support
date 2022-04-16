# PHP Support

![](https://img.shields.io/badge/php-^8.0-blue.svg)
![PHP Package](https://github.com/efureev/php-support/workflows/PHP%20Package/badge.svg?branch=master)
[![Build Status](https://travis-ci.org/efureev/php-support.svg?branch=master)](https://travis-ci.org/efureev/php-support)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a53fb85fd1ab46169758e10dd2d818cb)](https://app.codacy.com/app/efureev/php-support?utm_source=github.com&utm_medium=referral&utm_content=efureev/php-support&utm_campaign=Badge_Grade_Settings)
[![Latest Stable Version](https://poser.pugx.org/efureev/support/v/stable?format=flat)](https://packagist.org/packages/efureev/support)
[![Total Downloads](https://poser.pugx.org/efureev/support/downloads)](https://packagist.org/packages/efureev/support)
[![Maintainability](https://api.codeclimate.com/v1/badges/a7cf8708bf58fa7e5096/maintainability)](https://codeclimate.com/github/efureev/php-support/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/a7cf8708bf58fa7e5096/test_coverage)](https://codeclimate.com/github/efureev/php-support/test_coverage)
[![codecov](https://codecov.io/gh/efureev/php-support/branch/v2/graph/badge.svg)](https://codecov.io/gh/efureev/php-support/tree/v2)

## Install

For php >= 8.0

```bash
composer require efureev/support "^4.0"
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
    - accessible
    - dataToArray
    - exists
    - fromPostgresArray
    - fromPostgresPoint (^4.8.0)
    - get
    - has
    - merge
    - remove
    - removeByValue
    - set
    - toArray
    - toIndexedArray
    - toPostgresArray
    - toPostgresPoint (^4.8.0)
    - replaceByTemplate
  + String
    - removeAccents (^4.9.0)
    - removeMultiSpace
    - replaceByTemplate
    - replaceStrTo
    - seemsUTF8 (^4.9.0)
    - slugify (^4.9.0)
    - toCamel
    - toDelimited
    - toKebab
    - toLowerCamel
    - toScreamingDelimited
    - toScreamingSnake
    - toSnake
    - truncate (^4.9.0)
  + Json
    - decode
    - encode
    - htmlEncode
  + Bit
    - addFlag
    - checkFlag
    - decBinPad
    - exist
    - grant
    - removeFlag
  + B64
    - decode
    - decodeSafe
    - encode
    - encodeSafe
  + Number
    - safeInt (^4.1.0)
- Global functions
  + does_trait_use (^4.4.0)
  + classNamespace
  + class_basename
  + class_uses_recursive
  + instance
  + isTrue
  + trait_uses_recursive
  + value
  + remoteCall (^4.3.1)
  + remoteStaticCall (^4.3.1)
  + remoteStaticCallOrTrow (^4.7.0)
  + when
- Exceptions
  + ConfigException
  + Exception
  + InvalidArgumentException
  + InvalidCallException
  + InvalidConfigException
  + InvalidParamException
  + InvalidValueException
  + JsonException
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
  + Command
  + Jsonable
  + Prototype
- Traits
  + ArrayStorage
  + ArrayStorageConfigurableTrait
  + ConfigurableTrait
  + ConsolePrint
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

## Test

```bash
composer test
composer test-cover # with coverage
```
