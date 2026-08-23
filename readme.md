<h1 align="center">PHP Support</h1>

<p align="center">
  <strong>The small things you write in every project, written once.</strong><br>
  Array and string helpers, collections, a dot-notation store, typed exceptions and a pile of traits — for PHP 8.5, with no runtime dependencies.
</p>

<p align="center">
  <a href="https://packagist.org/packages/efureev/support"><img src="https://img.shields.io/packagist/v/efureev/support" alt="Latest Stable Version"></a>
  <a href="https://www.php.net/releases/8.5/en.php"><img src="https://img.shields.io/badge/php-%3E%3D%208.5-blue" alt="PHP Version"></a>
  <a href="https://github.com/efureev/php-support/actions/workflows/php.yml"><img src="https://github.com/efureev/php-support/actions/workflows/php.yml/badge.svg?branch=master" alt="Build Status"></a>
  <a href="https://packagist.org/packages/efureev/support"><img src="https://img.shields.io/packagist/dt/efureev/support" alt="Total Downloads"></a>
  <a href="./LICENSE"><img src="https://img.shields.io/packagist/l/efureev/support" alt="License"></a>
</p>

---

```bash
composer require efureev/support
```

```php
use Php\Support\Helpers\Arr;
use Php\Support\Helpers\Str;
use Php\Support\Structures\Collections\ArrayCollection;

Arr::get($config, 'database.connections.pgsql.host', '127.0.0.1');

Str::slugify('Привет, мир!');                       // 'privet-mir'

(new ArrayCollection($orders))
    ->filter(static fn(array $o) => $o['paid'])
    ->groupBy('customer_id')
    ->map(static fn(ArrayCollection $c) => $c->sum('total'));
```

## Why

Most projects grow their own `helpers.php`: a dot-notation getter, a slugifier, a bit-mask check.
This package is that file, extracted and hardened — **98% test coverage, PHPStan level 7, and a
strict test run that fails on warnings and deprecations.**

- **No runtime dependencies.** Only `ext-ctype`, `ext-json` and `ext-mbstring`, all standard.
  `ext-intl` is optional and improves transliteration.
- **Multibyte-aware throughout.** Case conversion, padding, truncation and masking count
  characters, not bytes, and work in any script.
- **One exception hierarchy.** Everything thrown implements a single interface, so one `catch`
  covers the whole package.
- **Nothing you cannot address.** Global helper functions are convenient but shadowable; every one
  of them is also a static method on a class nothing can intercept.

## Install

```bash
composer require efureev/support
```

<details>
<summary>Installing on an older PHP</summary>

| PHP        | Version                                    |
|------------|--------------------------------------------|
| >= 8.5     | `composer require efureev/support "^6.1"`  |
| 8.4        | `composer require efureev/support "^5.3"`  |
| 8.1 – 8.3  | `composer require efureev/support "^4.19"` |
| 7.4 – 8.0  | `composer require efureev/support "^3.0"`  |
| 7.2 – 7.4  | `composer require efureev/support "^2.0"`  |

Upgrading a major? See [UPGRADE.md](./UPGRADE.md).
</details>

## Arrays

Read and write nested data without a pile of `isset()`.

```php
use Php\Support\Helpers\Arr;

$config = ['db' => ['host' => 'localhost', 'ports' => [5432, 5433]]];

Arr::get($config, 'db.host');            // 'localhost'
Arr::get($config, 'db.ports.0');         // 5432
Arr::get($config, 'db.user', 'postgres') // 'postgres' — the default
Arr::has($config, 'db.host');            // true

Arr::set($config, 'db.user', 'admin');   // returns the whole array
Arr::remove($config, 'db.ports');
```

Any separator you like, on every method:

```php
Arr::get($config, 'db/host', null, '/');
```

Reshape a result set:

```php
$users = [
    ['id' => 1, 'name' => 'Ada',   'team' => 'core'],
    ['id' => 2, 'name' => 'Linus', 'team' => 'core'],
    ['id' => 3, 'name' => 'Grace', 'team' => 'ops'],
];

Arr::pluck($users, 'name');            // ['Ada', 'Linus', 'Grace']
Arr::pluck($users, 'name', 'id');      // [1 => 'Ada', 2 => 'Linus', 3 => 'Grace']
Arr::keyBy($users, 'id');              // [1 => [...], 2 => [...], 3 => [...]]
Arr::only($users[0], ['id', 'name']);  // ['id' => 1, 'name' => 'Ada']
Arr::first($users, static fn(array $u) => $u['team'] === 'ops');
```

Flatten and unflatten:

```php
Arr::dot(['db' => ['host' => 'localhost']]);   // ['db.host' => 'localhost']
Arr::undot(['db.host' => 'localhost']);        // ['db' => ['host' => 'localhost']]
Arr::flatten([1, [2, [3, [4]]]]);              // [1, 2, 3, 4]
```

## Strings

Case conversion that understands more than ASCII:

```php
use Php\Support\Helpers\Str;

Str::toSnake('getHTTPResponse');   // 'get_http_response'
Str::toCamel('user_first_name');   // 'UserFirstName'
Str::toLowerCamel('user_id');      // 'userId'
Str::toKebab('BackgroundColor');   // 'background-color'

Str::toSnake('ПриветМир');         // 'привет_мир'  — not only Latin
Str::toCamel('über_straße');       // 'ÜberStraße'
```

URL-safe slugs, transliterating through ICU when `ext-intl` is available:

```php
Str::slugify('Привет, мир!');            // 'privet-mir'
Str::slugify('Il était une fois');       // 'il-etait-une-fois'
Str::slugify('Hello World', '_');        // 'hello_world'
```

Trimming, masking, padding — all counting characters, not bytes:

```php
Str::truncate('The quick brown fox', 9);  // 'The...'     — at a word boundary
Str::limit('The quick brown fox', 9);     // 'The quick...' — at an exact length
Str::mask('4111111111111111', '*', 4, 8); // '4111********1111'
Str::padLeft('7', 3, '0');                // '007'
Str::squish("  too   much \n space ");    // 'too much space'
```

Identifiers:

```php
Str::uuid();   // '9f8c1f1e-...' — RFC 4122 v4
Str::ulid();   // '01J...'       — sortable by creation time
Str::random(); // 16 random alphanumerics from the CSPRNG
```

## Collections

`ArrayCollection` is an ordered map: countable, iterable, `ArrayAccess`, JSON-serialisable.

```php
use Php\Support\Structures\Collections\ArrayCollection;

$orders = new ArrayCollection([
    ['id' => 1, 'customer' => 'ada',   'total' => 120, 'paid' => true],
    ['id' => 2, 'customer' => 'ada',   'total' => 80,  'paid' => true],
    ['id' => 3, 'customer' => 'linus', 'total' => 200, 'paid' => false],
]);

$orders->filter(static fn(array $o) => $o['paid'])->sum('total');  // 200
$orders->pluck('total')->median();                                 // 120
$orders->countBy('customer');                                      // ['ada' => 2, 'linus' => 1]
$orders->every(static fn(array $o) => $o['total'] > 0);            // true

$byCustomer = $orders->groupBy('customer');
$byCustomer->get('ada')->count();                                  // 2

json_encode($orders->pluck('id'));                                 // '[1,2,3]'
```

Chain and branch without breaking the pipeline:

```php
$orders
    ->when($onlyPaid, static fn($c) => $c->filter(static fn($o) => $o['paid']))
    ->sortBy('total', descending: true)
    ->take(10)
    ->values();
```

`HashCollection` is the string-keyed sibling, which derives a key from the element's class:

```php
use Php\Support\Structures\Collections\HashCollection;

$handlers = new HashCollection();
$handlers[] = new SendEmail();          // keyed by SendEmail::class
$handlers->get(SendEmail::class);
```

## Storage

A dot-notation bag, reachable as an object, an array or a loop.

```php
use Php\Support\Storage;

$storage = new Storage();

$storage->set('user.profile.name', 'Ada');
$storage->get('user.profile.name');    // 'Ada'
$storage->exist('user.profile');       // true

$storage['user.profile.name'];         // same value, ArrayAccess
$storage->user;                        // ['profile' => ['name' => 'Ada']]

foreach ($storage as $key => $value) { /* ... */ }

$storage->merge(['app' => ['debug' => true]])->only('app')->toArray();
```

## JSON, numbers, bits, base64

```php
use Php\Support\Helpers\{Json, Number, Bit, B64};

Json::decodeOrThrow('{"a":1}');   // ['a' => 1] — throws on invalid input, unlike decode()
Json::prettyPrint(['a' => 1]);
Json::isValid('{oops');           // false

Number::humanize(1536);           // '1.5 KB'
Number::ordinal(22);              // '22nd'
Number::clamp(15, 0, 10);         // 10
Number::percentage(1, 3);         // 33.33
Number::safeInt('9007199254740993'); // stays a string: too big for JavaScript

const READ = 1, WRITE = 2, ADMIN = 4;

$perm = Bit::grant([READ, WRITE]);
Bit::checkFlag($perm, WRITE);     // true
Bit::hasAll($perm, [READ, WRITE]);// true
Bit::flags($perm);                // [1, 2]

B64::encodeSafe($binary);         // RFC 4648 URL-safe, readable by atob() and Python
```

## Enums

Two traits add the lookups PHP's enums leave out.

```php
use Php\Support\Enums\WithEnhancesForStrings;

enum Status: string
{
    use WithEnhancesForStrings;

    case Draft     = 'draft';
    case Published = 'published';
}

Status::values();                  // ['draft', 'published']
Status::names();                   // ['Draft', 'Published']
Status::labels();                  // ['draft' => 'Draft', 'published' => 'Published']
Status::hasValue('draft');         // true
Status::tryFromName('Draft');      // Status::Draft — from() only looks at values
Status::casesToEscapeString();     // "'draft', 'published'" — ready for an SQL IN list
```

## Exceptions

Every exception implements one interface, so a single `catch` covers the package even though the
concrete classes extend different SPL bases.

```php
use Php\Support\Exceptions\ExceptionInterface;

try {
    Json::decodeOrThrow($payload);
    Str::truncate($title, $length);
} catch (ExceptionInterface $e) {
    // InvalidValueException, InvalidParamException, MissingPropertyException, ...
}
```

## Traits

```php
use Php\Support\Traits\{Maker, UseErrorsBox, Metable};

final class Report
{
    use Maker;         // Report::make(...$args)
    use UseErrorsBox;  // addError(), errors(), firstError(), hasErrors()
    use Metable;       // withMeta(), metaAttribute()
}

Report::make()
    ->withMeta(['source' => 'cron'])
    ->addError('row 12 is malformed');
```

`HasPrePostActions` gives an object named callback groups to run around an operation. A callback
returning exactly `false` stops the group and makes `runActions()` return `false`, which is how a
listener vetoes the work:

```php
use Php\Support\Traits\HasPrePostActions;

final class Importer
{
    use HasPrePostActions;

    public function import(array $rows): bool
    {
        if (!$this->runActions('before', $rows)) {
            return false;                       // a listener said no
        }

        // ... do the work

        $this->runActions('after', count($rows));

        return true;
    }
}

$importer = (new Importer())
    ->addCallbackAction('before', static fn(array $rows) => $rows !== [])
    ->addCallbackAction('after', static fn(int $count) => $logger->info("imported {$count}"));

$importer->import([]);              // false — the guard vetoed it, nothing ran
$importer->import([['id' => 1]]);   // true
```

<details>
<summary>All traits</summary>

| Trait                    | What it gives you                                                     |
|--------------------------|-----------------------------------------------------------------------|
| `Maker`                  | `::make(...)` static constructor                                      |
| `Singleton`              | `::getInstance()`, protected constructor, unserialize guard           |
| `Thrower`                | `::throw()`, `::throwIf()` on an exception class                      |
| `UseErrorsBox`           | collect errors on an object: `addError`, `firstError`, `errorsCount`  |
| `Metable`                | attach metadata: `withMeta`, `metaAttribute`, `setMetaAttribute`      |
| `UseStorage`             | back an object with a `Storage`, reachable as properties              |
| `UseConfigurableStorage` | `configurable()` plus a storage fallback for unknown keys             |
| `ConfigurableTrait`      | configure an object from an array through setters or properties       |
| `ReadOnlyProperties`     | expose chosen non-public properties for reading                       |
| `HasPrePostActions`      | register and run callback groups around an operation                  |
| `Whener`                 | `when($condition, $callback)` for fluent chains                       |
| `ConsolePrint`           | `print()` / `printError()`, working outside the CLI SAPI too          |
| `TraitBooter`            | Eloquent-style `bootXxx()` hooks for traits                           |
| `TraitInitializer`       | `initializeXxx()` hooks, run per instance                             |
</details>

## Conditional handlers

`ConditionalHandler` pairs a piece of work with the condition that guards it, so the two travel
together and the work is only built when it is actually needed. Both closures receive the same
arguments.

```php
use Php\Support\ConditionalHandler;

$auditLink = ConditionalHandler::make(
    static fn(User $user) => ['label' => 'Audit log', 'href' => '/audit'],
)->handleIf(static fn(User $user) => $user->isAdmin());

$auditLink($admin);   // ['label' => 'Audit log', 'href' => '/audit']
$auditLink($guest);   // null — the handler was never called
```

Useful for assembling a menu, a set of API fields or a list of tabs, where each entry knows for
itself whether it belongs:

```php
$fields = array_filter(array_map(
    static fn(ConditionalHandler $field) => $field($user),
    [$auditLink, $billingLink, $profileLink],
));
```

The condition can also be a plain boolean, and the handler is immutable — `handleIf()` returns a
new instance rather than mutating the old one:

```php
ConditionalHandler::make(static fn() => buildReport(), $featureEnabled);
```

## PostgreSQL types

```php
use Php\Support\Helpers\Arr;
use Php\Support\Types\GeoPoint;

Arr::toPostgresArray(['a,b', 'plain', null]);   // '{"a,b",plain,NULL}'
Arr::fromPostgresArray('{"a,b",plain}');        // ['a,b', 'plain']

$point = GeoPoint::castFromDatabase('(37.6,55.7)');
$point->toJson();                               // '{"longitude":37.6,"latitude":55.7}'
```

Elements are quoted only when the array literal requires it, and escaping is handled — a value
containing a comma stays one element.

## Testing helpers

Two traits for your own test suite. `AdditionalAssertionsTrait` needs `phpunit/phpunit`, declared
in `suggest`.

```php
use Php\Support\Testing\{AdditionalAssertionsTrait, TestingHelper};

final class ReportTest extends TestCase
{
    use AdditionalAssertionsTrait;
    use TestingHelper;

    public function testItIsMakeable(): void
    {
        static::assertClassUsesTraits(Report::class, [Maker::class]);

        self::assertSame('hidden', static::runProtectedMethod(new Report(), 'internal'));
        self::assertSame(1, static::getProperty(new Report(), 'privateCounter'));
    }
}
```

## Global functions

The helpers are also available as global functions, and as static methods on `Php\Support\Func`:

```php
dataGet($order, 'customer.address.city');
Func::dataGet($order, 'customer.address.city');   // identical
```

Prefer the class in library code. Every global is declared under `function_exists()`, so a name
another package claimed first — Laravel defines `value()`, `class_basename()` and
`class_uses_recursive()` — wins silently, which makes behaviour depend on autoload order. `Func`
cannot be shadowed.

<details>
<summary>Full API reference</summary>

**`Arr`** — accessible, collapse, crossJoin, dataToArray, divide, dot, duplicates, except, exists,
fillKeysByValues, first, flatten, fromPostgresArray, fromPostgresArrayWithBraces, fromPostgresPoint,
get, has, isAssoc, isList, keyBy, last, map, merge, only, pluck, prepend, random, remove,
removeByValue, replaceByTemplate, set, shuffle, sortRecursive, toArray, toIndexedArray,
toPostgresArray, toPostgresPoint, undot, where, whereNotNull, wrap

**`Str`** — after, before, between, clearCache, contains, endsWith, isRegExp, lcFirst, limit, mask,
padBoth, padLeft, padRight, random, removeAccents, removeMultiSpace, replaceByTemplate,
replaceStrTo, slugify, slugifyWithFormat, squish, startsWith, title, toCamel, toCamelInitCase,
toDelimited, toKebab, toLowerCamel, toScreamingDelimited, toScreamingSnake, toSnake, trimPrefix,
trimSuffix, truncate, ucFirst, ulid, uuid, wrap

**`Json`** — decode, decodeOrThrow, encode, encodeOrThrow, htmlEncode, isValid, prettyPrint

**`Number`** — clamp, format, humanize, isInteger, ordinal, percentage, safeInt, `MAX_SAFE_INTEGER`

**`Bit`** — addFlag, checkFlag, decBinPad, flags, grant, hasAll, hasAny, hasFlagIn, removeFlag,
toggleFlag

**`B64`** — decode, decodeOrThrow, decodeSafe, encode, encodeSafe

**`URLify`** — downcode, seemsUTF8

**`ArrayCollection`** — add, all, avg, chunk, clear, clone, collapse, concat, contains, containsKey,
count, countBy, current, diff, each, every, exists, filter, findFirst, first, flatten, flip, get,
getIterator, getKeys, getValues, groupBy, implode, indexOf, intersect, isEmpty, key, keyBy, last,
lazy, map, mapByKey, mapInto, max, median, merge, min, next, partition, pipe, pluck, prepend, push,
random, reduce, reject, remove, removeElement, reverse, set, skip, slice, some, sort, sortBy,
sortDesc, sortKeys, sum, take, tap, testForAll, toArray, toJson, transform, unique, unless, values,
when, whereInstanceOf, zip

**`HashCollection`** — add, all, clear, contains, count, each, filter, find, get, getIterator,
groupBy, hasKey, implode, isEmpty, keys, map, partition, reduce, remove, set, sortBy, toArray, values

Both collections also implement `ArrayAccess` and `JsonSerializable`; those methods are left out of
the lists above.

**`Storage`** — all, clear, count, countRecursive, except, exist, get, isEmpty, merge, only, remove,
set, toArray, plus property access, `ArrayAccess` and `foreach`

**`Func`** — attributeToGetterMethod, attributeToSetterMethod, classBasename, classNamespace,
classUsesRecursive, dataGet, doesTraitUse, eachValue, findGetterMethod, findSetterMethod,
getPropertyValue, instance, isTrue, mapValue, publicPropertyExists, remoteCall, remoteStaticCall,
remoteStaticCallOrThrow, traitUsesRecursive, value, when

**Exceptions** — ExceptionInterface, plus ConfigException, Exception, InvalidArgumentException,
InvalidCallException, InvalidConfigException, InvalidParamException, InvalidValueException,
MethodNotAllowedException, MissingClassException, MissingConfigException, MissingMethodException,
MissingPropertyException, NotSupportedException, UnknownMethodException, UnknownPropertyException

**Enums** — casesToEscapeString, casesToString, fromName, hasName, hasValue, labels, names, random,
toKeyValueArray, toValueKeyArray, tryFromName, values

**`ConditionalHandler`** — make, handleIf, resolve, `__invoke`

**Types** — Point, GeoPoint · **Interfaces** — Arrayable, Jsonable
</details>

## Good to know

**Install `ext-intl` if you slugify non-Latin text.** Without it the bundled character maps cover
Latin, Cyrillic and Greek, but not the `Œ`/`œ` ligature or CJK.

**`Arr::fromPostgresArray` cannot restore a `null`.** The writer emits the `NULL` keyword correctly,
but the reader returns it as the string `'NULL'`, so a round trip is not value-preserving for null
elements.

**`Str` caches its case conversions** in a bounded, process-wide cache. Call `Str::clearCache()` in
a long-running worker if you convert unbounded user input.

## Contributing

Bug reports and pull requests are welcome — see [CONTRIBUTING.md](./CONTRIBUTING.md).

```bash
composer test         # PHPStan + PHPUnit
composer test-cover   # with coverage
composer phpcs        # coding standard
composer cs-fix       # fix what can be fixed automatically
```

## License

MIT — see [LICENSE](./LICENSE).
