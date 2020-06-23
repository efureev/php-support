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
    - get
    - set
    - exists
    - has
    - remove
    - removeByValue
    - toArray
    - dataToArray
    - merge
    - fromPostgresArray
    - toPostgresArray
    - toIndexedArray
    - accessible
    - replaceByTemplate
  + String
    - toSnake
    - toDelimited
    - toScreamingDelimited
    - toScreamingSnake
    - toKebab
    - toCamel
    - toLowerCamel
    - removeMultiSpace
    - replaceStrTo
    - replaceByTemplate
  + Json
    - htmlEncode
    - encode
    - decode
  + Bit
    - removeFlag
    - addFlag
    - checkFlag
    - exist
    - grant
    - decBinPad
  + B64
    - encode
    - decode
    - encodeSafe
    - decodeSafe
- Global functions
  + value
  + classNamespace
  + isTrue
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
  + Jsonable
  + Prototype
  + Command
- Traits
  + Maker
  + Metable
  + ConfigurableTrait
  + ArrayStorage
  + ArrayStorageConfigurableTrait
  + Singleton
  + ConsolePrint
  + Whener
- Types
  + Point
  + GeoPoint

## Test

```bash
composer test
composer test-cover # with coverage
```
