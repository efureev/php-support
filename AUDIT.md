# Аудит пакета `efureev/support`

**Аудит проведён:** 2026-08-23 на ревизии `c0c745e` · **Актуальная версия:** 5.3.0
**Требования пакета:** PHP `^8.4`, `ext-ctype`, `ext-json`, `ext-mbstring`

> **Статус.** Блоки 5.2.x и 5.3.0 реализованы (см. `CHANGELOG.md`, секции `v5.2.1` и `v5.3.0`).
> **Из этого файла удалено всё, что уже сделано** — здесь остались только открытые находки.
> История закрытых лежит в git.

---

## 1. Резюме

`efureev/support` — библиотека общего назначения: хелперы (`Arr`, `Str`, `Json`, `Bit`, `B64`,
`Number`, `URLify`), коллекции, трейты, исключения, типы. 53 файла в `src/`, 4074 NCLOC.

### Текущее состояние (замерено после блока 5.3.0)

| Метрика                      | Значение                       | Комментарий                                       |
|------------------------------|--------------------------------|---------------------------------------------------|
| Тесты                        | **629 зелёных**, 3403 ассерта  | 0 deprecation, 0 notice; все `failOn*` включены   |
| Покрытие строк               | **97.52%** (1260/1292)         | ни одного файла ниже 83%                          |
| Покрытие методов             | **94.67%** (320/338)           |                                                   |
| PHPStan                      | **level 6, 0 ошибок**          | + baseline из 12 (пределы дженериков)             |
| PHPCS (PSR-12)               | 0 errors, 0 warnings           |                                                   |
| `composer validate --strict` | OK                             |                                                   |

### Главный вывод

Системные проблемы аудита закрыты. PHPStan поднят со level 2 до 6 и больше не исключает
`ArrayCollection`; покрытие выросло с 81.9% до 97.52%; сборка честная — падает на warning'ах и
deprecation'ах из собственного кода.

Осталось **6 находок P1**, **6 P2** и **10 кандидатов на удаление** — почти всё это ломающие
изменения, ждущие 6.0: нейминг, сигнатуры, замена вендоренного `URLify`, перенос `@template`
с классов на методы. Ни одна не приводит к потере данных.

---

## 2. Методика

Проверялось:

1. Полное чтение `src/` (53 файла), `phpstan.neon`, `phpunit.xml`, `.phpcs.xml`,
   `composer.json`, `.github/workflows/`, `readme.md`, `CHANGELOG.md`.
2. Разбор `clover.xml` — покрытие по каждому файлу.
3. Прогон инструментов:
   ```bash
   ./vendor/bin/phpunit --no-coverage --no-logging --display-deprecations --display-notices
   ./vendor/bin/phpstan analyse src --level=9 --no-progress
   ./vendor/bin/phpcs -q --report=full
   composer validate --strict && composer outdated --direct
   ```
4. **Каждая находка из разделов 3–4 воспроизведена запуском PHP-кода** на PHP 8.5.9 с реальным автозагрузчиком пакета. В
   отчёте приводится фактический вывод, а не рассуждение.
5. Программная сверка README со списком публичных методов через Reflection.

### Как читать находки

Приоритеты:

* **P0** — потеря или порча данных. Все закрыты в 5.2.x, в файле не осталось.
* **P1** — некорректное поведение на краевых входах; ошибки видны как `Warning`/`TypeError`.
* **P2** — пробел бизнес-логики: API делает не то, что обещает имя или docblock.

---

## 3. Существенные дефекты (P1)

### Хелпер `Str`

#### P1-1 · `slugify()` оставляет висящий разделитель

**Где:** `src/Helpers/Str.php:305`, `slugifyWithFormat()` — `Str.php:310`

```php
Str::slugify('Hello World!');      // 'hello-world-'   ← хвостовой дефис
Str::slugify('  --Hello--  ');     // '-hello-'        ← дефис с обеих сторон
```

**Влияние.** Slug'и вида `hello-world-` попадают в URL и уникальные индексы. При сравнении
`slugify($a) === slugify($b)` строки, отличающиеся только пунктуацией на границах, дают разные результаты.

**Исправление.** После `preg_replace` добавить схлопывание повторов и `trim($slug, $separator)`. Заложить фикс как
**breaking** (меняет выдачу) либо ввести флаг `$trim = true`.

---

#### P1-5 · Разбиение на слова не поддерживает не-ASCII

**Где:** `src/Helpers/Str.php:50` (`toScreamingDelimited`), `Str.php:167` (`toCamelInitCase`)

```php
Str::toSnake('ПриветМир');    // 'приветмир'   ← границы слов не найдены
Str::toSnake('ÜberStraße');   // 'über_straße' ← сработало только на латинской S
```

**Что не так.** Границы слов ищутся посимвольными сравнениями `$letter >= 'A' && $letter <= 'Z'`, то есть по
ASCII-диапазону, хотя сами символы извлекаются `mb_substr()` в UTF-8. Кириллица, греческий, диакритика вне диапазона не
распознаются как заглавные.

**Влияние.** Молчаливо неверный результат — не ошибка, а «почти правильная» строка. Метод подан в README как
мультибайтовый (`toSnake`, `toKebab`, `toCamel`).

**Исправление.** Использовать `mb_strtoupper($ch) === $ch && mb_strtolower($ch) !== $ch` вместо диапазонных сравнений,
либо `preg_split` с `\p{Lu}`/`\p{Ll}` и модификатором `u`. Как минимум — задокументировать ограничение.

---

### Хелпер `Bit`

#### P1-7 · `decBinPad()`: `$length` — минимум, а не ширина

**Где:** `src/Helpers/Bit.php:104`

```php
Bit::decBinPad(-1, 8);              // 64 символа единиц (дополнение до двух)
strlen(Bit::decBinPad(PHP_INT_MAX, 4));  // 63
```

**Что не так.** `str_pad()` только дополняет, но не обрезает. Название и docblock («with left pad zero-filling») создают
впечатление фиксированной ширины. Отрицательные числа не поддержаны, но и не отклонены.

**Исправление.** Явно указать в docblock, что `$length` — минимальная ширина. Либо добавить
`$strict = false`: при `true` — исключение, если результат длиннее `$length`, и отказ для отрицательных значений.

---

### Хелпер `Number`

#### P1-8 · `safeInt()` противоречит собственному docblock

**Где:** `src/Helpers/Number.php:26`, docblock — `Number.php:19`

Docblock обещает: *«Non-integer values are returned as string»*. Фактически:

```php
Number::safeInt('1.9');    // 1        ← int, значение усечено
Number::safeInt('abc');    // 'abc'    ← совпадает с docblock
```

**Что не так.** Ветка `return is_numeric($value) ? (int)$value : (string)$value;`
(`Number.php:41`) приводит дробную числовую строку к `int` с потерей дробной части. Метод предназначен для безопасного
пересечения с `Number.MAX_SAFE_INTEGER` в JS — молчаливое усечение `'1.9'` → `1` в этом контексте особенно опасно.

**Исправление.** Привести реализацию к docblock: нецелые числовые строки возвращать как есть (строкой). Либо исправить
docblock и явно задокументировать усечение. Первое предпочтительнее.

---

### Хелпер `Arr`

### Коллекции

#### P1-18 · `HashCollection` нарушает контракт `ArrayAccess`

**Где:** `src/Structures/Collections/HashCollection.php:137`

```php
$h = new HashCollection();
$h[] = 'str';
// TypeError: HashCollection::add(): Argument #1 ($element) must be of type object, string given
```

**Что не так.** `offsetSet(null, $value)` делегирует в `add(object $element)`, который принимает только объекты (ключом
становится `$element::class`). Идиома `$collection[] = $value`, обязательная для `ArrayAccess`, не работает для
скаляров, хотя `set()` их принимает. Шаблоны `T` (значение) и
`TO of object` (элемент для `add`) конфликтуют между собой.

**Исправление.** Либо ограничить `T` объектами и объявить это в типах, либо в `offsetSet(null, …)`
бросать `InvalidParamException` с внятным сообщением, либо генерировать ключ для не-объектов.

---

### Кодирование

#### P1-20 · `B64::decode()` не отличает битый вход от пустого

**Где:** `src/Helpers/B64.php:48`

```php
B64::decode('!!!!');   // ''   (при $strict = false — по умолчанию)
```

**Что не так.** `base64_decode($data, false)` игнорирует недопустимые символы и возвращает `''`, а не `false`, поэтому
проверка `!== false` не срабатывает. `decodeSafe()`
(`B64.php:78`) всегда вызывает `decode()` без strict — жёстко нестрогий режим.

**Дополнительно (интероперабельность).** Алфавит URL-safe в пакете —
`'-_~'` (`B64.php:26`): `+`→`-`, `/`→`_`, **`=`→`~`**. RFC 4648 §5 определяет только `-` и `_`, а padding либо
сохраняется, либо отбрасывается. Символ `~` — нестандартный: строки, закодированные
`B64::encodeSafe()`, не декодируются сторонними реализациями (JS `atob`, Python
`urlsafe_b64decode`, Go `base64.URLEncoding`) и наоборот.

**Исправление.** `$strict = true` по умолчанию (breaking, в 6.0); `decodeSafe(bool $strict = true)`; перейти на RFC 4648
(`rtrim($s, '=')` при кодировании, восстановление padding при декодировании), оставив поддержку `~` для обратной
совместимости на один мажор.

---

## 4. Пробелы бизнес-логики (P2)

### P2-7 · `ReadOnlyProperties` открывает наружу `private`/`protected` свойства

**Где:** `src/Traits/ReadOnlyProperties.php:11`

```php
class R { use ReadOnlyProperties; private string $secret = 's3cr3t'; }
(new R)->secret;   // 's3cr3t'
```

**Что не так.** `__get()` вызывается при обращении к недоступному свойству. Проверка
`property_exists($this, $key)` истинна и для `private`, а чтение `$this->$key` происходит в области видимости класса —
доступ разрешён. Трейт делает **все** свойства публично читаемыми.

**Влияние.** Разрушение инкапсуляции. Название обещает «read-only» (запретить запись), а фактическая семантика —
«сделать всё публичным на чтение». Секреты, внутреннее состояние, служебные поля утекают наружу; сериализаторы и дамперы
начинают их видеть.

**Исправление.** Ограничить публичными свойствами (`(new \ReflectionProperty($this, $key))->isPublic()`) или явным
allow-list (`protected array $readOnlyProperties = [...]`). Это breaking — в 6.0.

---

### P2-8 · Трейты энумов: несовместимые сигнатуры и неверный `@mixin`

**Где:** `src/Enums/WithEnhances.php:11`, `src/Enums/WithEnhancesForStrings.php:17`

```php
WithEnhances::casesToString(callable $decorator, string $delimiter = ', ');
WithEnhancesForStrings::casesToString(string $delimiter = ', ', ?callable $decorator = null);
```

**Проблемы:**

1. **Разные сигнатуры одного имени.** Код, работающий с энумом на `WithEnhances`, ломается при переходе на
   `WithEnhancesForStrings` (аргументы меняются местами). Алиасинг
   `casesToString as casesToStringBase` прячет фатальную ошибку, но не решает несовместимость API.
2. **`@mixin \UnitEnum` неверен.** `values()` (`WithEnhances.php:24`) и `toValueKeyArray()`
   (`:58`) обращаются к `$case->value`, которого у `UnitEnum` (pure enum) нет → `Error` в рантайме. Должно быть
   `@mixin \BackedEnum` либо разделение трейтов.
3. **`toValueKeyArray(): array<string, string>`** — для int-backed энума ключи и значения `int`, аннотация неверна.
4. **`hasValue()` есть только в `WithEnhancesForStrings`** и типизирован `string $value`. Для int-backed энумов метода
   нет вовсе, хотя README подаёт `hasValue` как общий метод энумов.
5. **`hasName(string $value)`** (`WithEnhances.php:37`) — параметр назван `$value`, хотя это имя.

**Исправление.** Единая сигнатура `casesToString(?callable $decorator = null, string $delimiter = ', ')`
в обоих трейтах; `@mixin \BackedEnum`; `hasValue(string|int $value)` перенести в `WithEnhances`; переименовать параметр
в `$name`. Пункты 1 и 5 — breaking, в 6.0.

---

### P2-11 · Глобальные функции: конфликты и разнобой в неймингах

**Где:** `src/Global/base.php` (21 функция, автозагрузка через `files`)

**Конфликты.** Все функции обёрнуты в `if (!function_exists(...))`. При установке рядом с Laravel/Illuminate часть имён
(`value`, `class_basename`, `trait_uses_recursive`,
`class_uses_recursive`) уже занята — тогда **используется чужая реализация**, а поведение пакета молча меняется в
зависимости от порядка автозагрузки. `class_uses_recursive()` в этом пакете (`base.php:220`) и в Laravel возвращают
структуры с разными ключами. Имя `when()` (`base.php:115`)
— крайне общее и с высокой вероятностью столкновения.

**Нейминг.** Три стиля в одном файле:

| snake_case                                                                                                   | camelCase                                                                                                                                                           |
|--------------------------------------------------------------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `class_basename`, `class_uses_recursive`, `trait_uses_recursive`, `does_trait_use`, `public_property_exists` | `dataGet`, `mapValue`, `eachValue`, `classNamespace`, `isTrue`, `remoteCall`, `remoteStaticCall`, `getPropertyValue`, `attributeToGetterMethod`, `findGetterMethod` |

Плюс асимметрия пары: `findGetterMethod()` против `findSetterMethodByProp()`.

**Прочее.** В `base.php` нет `declare(strict_types=1)` (единственный файл `src/` без него, не считая
`ConfigurableTrait.php`). Покрытие файла — **47.9%**.

**Исправление.** Задокументировать в README риск конфликта и рекомендацию не полагаться на глобальные функции в
библиотечном коде. Продублировать всё как статические методы (`Php\Support\Helpers\Fn::dataGet()` и т.п.) — глобальные
оставить тонкими прокси. Унифицировать нейминг в 6.0 с deprecated-алиасами.

---

### P2-12 · `Point::castFromDatabase()` — фабрика, объявленная как метод экземпляра

**Где:** `src/Types/Point.php:91`

```php
public function castFromDatabase(?string $value): ?self
{
    ...
    return new static((float)$x, (float)$y);   // не использует $this
}
```

Метод не обращается к `$this`, но требует существующего объекта: `(new Point)->castFromDatabase($s)`. Симметричные
фабрики (`fromArray`, `fromJson`) — статические.

**Исправление.** Сделать `public static function castFromDatabase()` (breaking — в 6.0).

---

### P2-13 · Мелкие контрактные шероховатости

| Место                                                | Проблема                                                                                                                                                            |
|------------------------------------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `src/Traits/Thrower.php:12`                          | `throw(): void` — должно быть `never`, иначе PHPStan/IDE не понимают, что поток управления прерван                                                                  |
| `src/Traits/Maker.php:19`                            | `make(...$arguments)` без типа возврата + `@phpstan-ignore-next-line`. Должно быть `: static`                                                                       |
| `src/Traits/Singleton.php:26`                        | `getInstance(): self` — для наследников статический анализ выводит базовый класс. Должно быть `: static`                                                            |
| `src/Traits/TraitInitializer.php:53`                 | `static::$traitInitializers[static::class]` читается без `isset` — `Undefined array key`, если `initializeTraits()` вызван без предварительного `bootIfNotBooted()` |
| `src/Helpers/Str.php:263`                            | `replaceByTemplate(string $str, …): array\|string` — вход `string`, `str_replace()` всегда вернёт `string`. Тип возврата излишне широк                              |
| `src/Helpers/Bit.php:79`                             | `exist(array $list, int $bit)` — тривиальная обёртка `checkFlag(grant($list), $bit)` со сломанным неймингом                                                         |
| `src/Helpers/Str.php:356`                            | `seemsUTF8()` — чистое проксирование в `URLify::seemsUTF8()`                                                                                                        |
| `src/Global/base.php:61`                             | `dataGet()` использует `isset($target->{$segment})` — публичное свойство со значением `null` даёт `$default` вместо `null`                                          |

---

## 5. Типизация и статический анализ

### P2-15 · Дженерики объявлены там, где не работают

**Где:** `src/Helpers/Arr.php:36-38`, `src/Traits/Metable.php:11`

```php
/**
 * @template TKey of array-key
 * @template T
 */
class Arr { /* все методы статические */ }
```

Шаблоны класса в PHPStan/Psalm связываются при **создании экземпляра**. У `Arr` экземпляров нет — все методы `static`.
Аннотации `@param array<TKey,T>` в десятках методов не выводят никаких типов, это декорация. Правильно — объявлять
`@template` на уровне метода.

Аналогично `@template TValue` на трейте `Metable` без параметризации при `use`.

**Симптом того же:** PHPStan level 6 выдаёт 5 ошибок `paramOut.type`/`parameterByRef.type`
в `Arr::set()`/`Arr::remove()` — именно потому, что шаблоны класса не резолвятся.

---

## 6. Тесты и качество

### Покрытие по файлам

Замер после блока 5.3.0: **97.52%** строк (1260/1292), **94.67%** методов (320/338).
Ни один файл не ниже 83%; всё, что раньше было на нуле, покрыто.

|  Покрытие | Файл                              | Что не покрыто                                              |
|----------:|-----------------------------------|-------------------------------------------------------------|
| **83.3%** | `src/Traits/ConsolePrint.php`     | ветка `php://stdout` вне CLI — в тестах SAPI всегда CLI      |
| **85.7%** | `src/Traits/Singleton.php`        | `__clone()`                                                  |
| **90.9%** | `src/Traits/UseStorage.php`       | краевые ветки `__unset` на неинициализированных свойствах    |
| **92.3%** | `src/Types/GeoPoint.php`          | ветка отказа `toJson()`                                      |
| **95.5%** | `src/Helpers/Arr.php`             | краевые ветки PG-парсера                                     |

Прогон строгий: `failOnWarning`, `failOnDeprecation`, `failOnNotice`, `failOnRisky`. Deprecation'ы,
исходящие из зависимостей, исключены через `ignoreIndirectDeprecations` — иначе `--prefer-lowest`
на PHP 8.5 роняет сборку из-за чужого кода.

Что осталось: тесты не анализируются PHPStan (см. P2-15) и покрытие не грузится в внешний сервис.

## 7. Чего не хватает

### 7.1 API — хелперы

**`Arr`:** `sortRecursive()`, `divide()`, `crossJoin()`, `shuffle()`, `whereNotNull()`.

**`Str`:** `padBoth()`, `wrap()`, `title()`, `uuid()`/`ulid()`.
`studly()` намеренно не добавлен: `toCamel()` уже даёт StudlyCase.

**`Json`:** `prettyPrint()`.

**`Number`:** `format()`, `clamp()`, `percentage()`, `humanize()`, `ordinal()`.

**`Bit`:** `toggleFlag()`, `flags(int $mask): int[]`, `hasAll()`/`hasAny()`.

**`B64`:** RFC 4648-совместимый режим (P1-20).

### 7.2 API — коллекции

`median()`, `flip()`, `zip()`, `every()`/`some()`, `countBy()`,
`lazy()`/`Generator`-обёртка для больших наборов.

Для `HashCollection` — `sortBy`, `partition`, `groupBy` по аналогии с `ArrayCollection`.

### 7.3 API — прочее

* `WithEnhances::random()`.
* `Storage`: `merge()`, `only()`, `except()`.
* Отсутствуют как категории: работа с датой/временем, `Uuid`/`Ulid`, `Result`/`Option`,
  `Pipeline`, `Macroable`, `Comparable`, `Money`/`Decimal`, кэш-абстракция. Это не обязательные пробелы — но пакет
  позиционируется как «collection of useful functions», а покрывает только строки/массивы/биты.

### 7.4 Инфраструктура

| Что                                                              | Зачем                                                                                                  |
|------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| Загрузка покрытия в codecov или Coveralls                        | сейчас покрытие в CI не собирается вовсе                                                               |
| `composer normalize` в скриптах                                  | плагин подключён, скрипта нет                                                                          |
| `authors[].email`, `support.issues`, `homepage` в composer.json  | нет                                                                                                    |
| Анализ `tests/` под PHPStan                                       | требует ~200 правок аннотаций в фикстурах (см. P2-15)                                                  |
| Матрица CI с `ext-mbstring` off                                  | `URLify::seemsUTF8()` имеет ветку без mbstring (`@codeCoverageIgnore`), которая никогда не проверяется |

---

## 8. Что уже не нужно (легаси и мусор)

| #    | Кандидат                                                    | Почему                                                                                                                                                                                                                                                                                                                                                                                                                                              |
|------|-------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| L-4  | `remoteStaticCallOrTrow()` (`base.php:274`)                 | Алиас с опечаткой, уже `@deprecated`. Удалить в 6.0                                                                                                                                                                                                                                                                                                                                                                                                 |
| L-5  | `Interfaces/Prototype`                                      | Объявляет единственный метод `__clone()`, который есть у любого объекта PHP. Не реализован ни одним классом пакета, не несёт семантики                                                                                                                                                                                                                                                                                                              |
| L-6  | `Interfaces/Command`                                        | `execute(): void` — не используется ни в пакете, ни в тестах; дублирует общеизвестные контракты (`__invoke`, PSR-подобные Command). Без реализаций и документации бесполезен                                                                                                                                                                                                                                                                        |
| L-7  | `src/Testing/*` (2 трейта)                                  | Лежит в `src/`, но `AdditionalAssertionsTrait` требует `PHPUnit\Framework\TestCase` из `require-dev` — прод-код зависит от dev-зависимости. Покрытие 0%. `assertClassUsesTraits()` **переизобретает** уже имеющиеся в пакете `trait_uses_recursive()`/`class_uses_recursive()` инлайн-замыканиями. `TestingHelper` использует deprecated `setAccessible()`. Кандидат: вынести в `efureev/support-testing` или перевести на `suggest`/`autoload-dev` |
| L-8  | `Helpers/URLify.php` (794 строки, покрытие 9.3%)            | Вендоренная копия [jbroadway/urlify](https://github.com/jbroadway/urlify) без указания в `composer.json` — обновления безопасности и новые языковые карты не приходят. Заменяется `symfony/string` (`AsciiSlugger`) или `ext-intl` `Transliterator`. `public static array $maps` — публичное изменяемое глобальное состояние                                                                                                                        |
| L-9  | `Str::seemsUTF8()` (`Str.php:356`)                          | Однострочное проксирование в `URLify::seemsUTF8()`. Дубль публичного API                                                                                                                                                                                                                                                                                                                                                                            |
| L-10 | `Bit::exist()` (`Bit.php:79`)                               | Тривиальная обёртка `checkFlag(grant($list), $bit)` с грамматически неверным именем                                                                                                                                                                                                                                                                                                                                                                 |
| L-11 | `URLify::seemsUTF8Regex()` (ветка без mbstring)             | Помечена `@codeCoverageIgnore`, недостижима — `ext-mbstring` в `require` обязателен. Либо убрать требование расширения, либо удалить ветку                                                                                                                                                                                                                                                                                                          |
| L-15 | Аннотации `@psalm-*` в `ArrayCollection.php`                | Psalm не в зависимостях и не в CI — аннотации не читаются. Перевести на `@phpstan-*`                                                                                                                                                                                                                                                                                                                                                                |
| L-21 | Дублирование `ConfigurableTrait` ↔ `UseConfigurableStorage` | Теперь, когда `UseConfigurableStorage` переопределяет `applyValue()` и складывает неизвестные ключи в `Storage`, нужно решить, нужны ли оба трейта, или `ConfigurableTrait` полностью поглощается вторым                                                                                                                                                                                                                                                                                                                                            |

---

## 9. План по релизам

Оценка трудоёмкости: **S** ≤ 1 ч, **M** — 1–4 ч, **L** — больше дня.

### 6.0.0 · Major — ломающие изменения

| Задача                                                                                       | ID        | Оценка |
|----------------------------------------------------------------------------------------------|-----------|--------|
| Удалить `remoteStaticCallOrTrow()`                                                           | L-4       | **S**  |
| Удалить `Interfaces/Prototype`, `Interfaces/Command`                                         | L-5, L-6  | **S**  |
| Вынести `src/Testing/*` в отдельный пакет или в `autoload-dev`                               | L-7       | **M**  |
| Заменить вендоренный `URLify` на `symfony/string` / `ext-intl`                               | L-8, L-11 | **L**  |
| Единая сигнатура `casesToString()` в `WithEnhances*`; `@mixin \BackedEnum`; `hasName($name)` | P2-8      | **M**  |
| `ReadOnlyProperties` — только публичные свойства (или allow-list)                            | P2-7      | **M**  |
| `UseErrorsBox::setError()` удалить (в 5.3.0 помечен `@deprecated`)                           | —         | **S**  |
| `Str::slugify()` — trim и схлопывание разделителей                                           | P1-1      | **S**  |
| `Str::to*()` — корректные границы слов для не-ASCII                                          | P1-5      | **M**  |
| `Number::safeInt()` привести к docblock (нецелые — строкой)                                  | P1-8      | **S**  |
| `B64` — алфавит RFC 4648, `$strict = true` по умолчанию                                      | P1-20     | **M**  |
| `Point::castFromDatabase()` → `static`                                                       | P2-12     | **S**  |
| `HashCollection::offsetSet(null, …)` — внятный контракт                                      | P1-18     | **S**  |
| `Bit::exist()` → `hasFlagIn()` (алиас `@deprecated` в 5.3)                                   | L-10      | **S**  |
| Убрать `Str::seemsUTF8()` как дубль                                                          | L-9       | **S**  |
| Унифицировать нейминг глобальных функций; продублировать статическим классом                 | P2-11     | **L**  |
| `@template` перенести с классов на методы (`Arr`, `Metable`)                                 | P2-15     | **M**  |
| Решить судьбу `ConfigurableTrait` vs `UseConfigurableStorage`                                | L-21      | **M**  |
| PHPStan level 8+, пустой baseline, анализ `tests/`                                           | P2-15     | **L**  |
| `Bit::decBinPad()` — строгий режим / отказ для отрицательных                                 | P1-7      | **S**  |

**Оценка блока:** 2–3 недели.

---

## Приложение: команды для воспроизведения

```bash
# тесты (без записи артефактов в репозиторий)
./vendor/bin/phpunit --no-coverage --no-logging --display-deprecations --display-notices

# статический анализ как настроен (проходит) и как должно быть
./vendor/bin/phpstan analyse -c phpstan.neon --no-progress
./vendor/bin/phpstan analyse src --level=9 --no-progress

# стиль
./vendor/bin/phpcs -q --report=full

# покрытие
XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-text

# зависимости
composer validate --strict && composer outdated --direct
```
