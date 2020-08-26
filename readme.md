# PHP Support
![](https://img.shields.io/badge/php->=7.2-blue.svg)
![PHP Package](https://github.com/efureev/php-support/workflows/PHP%20Package/badge.svg?branch=master)
[![Build Status](https://travis-ci.org/efureev/php-support.svg?branch=master)](https://travis-ci.org/efureev/php-support)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/a53fb85fd1ab46169758e10dd2d818cb)](https://app.codacy.com/app/efureev/php-support?utm_source=github.com&utm_medium=referral&utm_content=efureev/php-support&utm_campaign=Badge_Grade_Settings)
[![Latest Stable Version](https://poser.pugx.org/efureev/support/v/stable?format=flat)](https://packagist.org/packages/efureev/support)
[![Total Downloads](https://poser.pugx.org/efureev/support/downloads)](https://packagist.org/packages/efureev/support)
[![Maintainability](https://api.codeclimate.com/v1/badges/a7cf8708bf58fa7e5096/maintainability)](https://codeclimate.com/github/efureev/php-support/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/a7cf8708bf58fa7e5096/test_coverage)](https://codeclimate.com/github/efureev/php-support/test_coverage)
[![codecov](https://codecov.io/gh/efureev/php-support/branch/v2/graph/badge.svg)](https://codecov.io/gh/efureev/php-support/tree/v2)

## Install

```bash
composer require efureev/support "^2.10"
```

## Content

- Helpers
  + Array
    - accessible
    - dataToArray
    - exists
    - fromPostgresArray
    - get
    - has
    - merge
    - remove
    - removeByValue
    - set
    - toArray
    - toIndexedArray
    - toPostgresArray
    - replaceByTemplate
  + String
    - removeMultiSpace
    - replaceByTemplate
    - replaceStrTo
    - toCamel
    - toDelimited
    - toKebab
    - toLowerCamel
    - toScreamingDelimited
    - toScreamingSnake
    - toSnake
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
- Global functions
  + classNamespace
  + isTrue
  + value
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
  + Whener
- Types
  + GeoPoint
  + Point

## Test

```bash
composer test
composer test-cover # with coverage
```
