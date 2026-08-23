# Upgrade guide

## 5.x → 6.0

### Requirements

PHP 8.5 or newer. `ext-intl` is not required but is strongly recommended: with it,
`Str::slugify()` and `Str::removeAccents()` transliterate the whole of Unicode instead of the
subset the bundled character maps cover.

### Removed

| Removed | Replacement |
|---|---|
| `remoteStaticCallOrTrow()` | `remoteStaticCallOrThrow()` — the misspelling was deprecated in 5.2.0 |
| `Php\Support\Interfaces\Prototype` | none; it declared only `__clone()`, which every object has |
| `Php\Support\Interfaces\Command` | none; a single `execute(): void`, unused |
| `UseErrorsBox::setError()` | `addError()` — deprecated in 5.3.0 |
| `Str::seemsUTF8()` | `URLify::seemsUTF8()` — the method was a one-line proxy |
| `Bit::exist()` | `Bit::hasFlagIn()` — same behaviour, a name that says what it does |
| `URLify::seemsUTF8Regex()` | none; it was the no-mbstring branch, unreachable since mbstring is required |

### Changed behaviour

These return different results for inputs they used to mishandle. Check any place where you store
or compare their output.

**`Str::slugify()` trims and collapses separators.**

```php
Str::slugify('Hello World!');   // 5.x: 'hello-world-'   6.0: 'hello-world'
Str::slugify('  --Hello--  ');  // 5.x: '-hello-'        6.0: 'hello'
```

**`Str::toSnake()` and friends split non-Latin words; `toCamel()` no longer discards them.**

```php
Str::toSnake('ПриветМир');   // 5.x: 'приветмир'   6.0: 'привет_мир'
Str::toCamel('ÜberStraße');  // 5.x: 'berStrae'    6.0: 'ÜberStraße'
```

The second one was silent data loss: every character outside the ASCII ranges was dropped.

**`Number::safeInt()` no longer truncates.**

```php
Number::safeInt('1.9');   // 5.x: 1 (int)   6.0: '1.9' (string)
```

**`B64` uses the RFC 4648 URL-safe alphabet.**

```php
B64::encodeSafe($data);  // 5.x: padding written as '~'   6.0: padding stripped
```

Output is now readable by `atob()`, Python's `urlsafe_b64decode` and Go. `decodeSafe()` still
accepts the old `~` form, so URLs minted by 5.x keep working.

`B64::decode()` is strict by default and returns `null` for invalid input instead of `''`. Pass
`false` as the second argument for the old lenient behaviour, or use the new `decodeOrThrow()`.

**`URLify::downcode()` transliterates more.** With `ext-intl` installed, text the bundled maps left
untouched — the `Œ`/`œ` ligature, CJK, Arabic — is now transliterated.

### Signature changes

**`WithEnhancesForStrings::casesToString()` takes its arguments in the other order**, matching
`WithEnhances`:

```php
// 5.x
Status::casesToString('|', $decorator);
// 6.0
Status::casesToString($decorator, '|');
```

Both arguments are optional; the default decorator is the case value.

**`Point::castFromDatabase()` is static.**

```php
(new Point())->castFromDatabase($value);  // 5.x
Point::castFromDatabase($value);          // 6.0
```

**`Bit::decBinPad()` rejects negative values**, and `HashCollection::offsetSet()` throws
`InvalidParamException` — instead of a bare `TypeError` — when you append a non-object without a key.

### `ReadOnlyProperties` exposes nothing by default

The trait used to return any declared property, private ones included, so every internal field was
publicly readable. It now serves an explicit allow-list:

```php
class User
{
    use ReadOnlyProperties;

    protected string $name   = 'John';
    private string $password = 'secret';

    protected function readOnlyProperties(): array
    {
        return ['name'];
    }
}
```

Without the override the trait exposes nothing. The allow-list is a method rather than a property
because PHP requires a property declared in both a trait and the using class to be defined
identically.

### The global functions now live in a class

`Php\Support\Func` holds the implementations; the global functions are thin wrappers over it and
keep their names, so existing calls continue to work.

Prefer the class where it matters: every global is declared under `function_exists()`, so a name
already taken — Laravel defines `value()`, `class_basename()` and `class_uses_recursive()` — wins
silently, and which implementation runs depends on autoload order.

```php
value($x);              // still works
Func::value($x);        // cannot be shadowed
```

One name differs: `findSetterMethodByProp()` is `Func::findSetterMethod()`, symmetric with
`findGetterMethod()`. The global keeps its old name.
