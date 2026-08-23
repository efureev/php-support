# Аудит пакета `efureev/support`

**Дата:** 2026-08-23 · **Ревизия:** `c0c745e` (ветка `master`) · **Версия пакета:** 5.2.0 **Требования пакета:** PHP
`^8.4`, `ext-mbstring`

> **Статус.** Блок 5.2.x из раздела 12 реализован в ветке `fix/5.2.x-audit` — см. `CHANGELOG.md`,
> секция `v5.2.1`. Блоки 5.3.0 и 6.0.0 не начаты.

---

## 1. Резюме

`efureev/support` — библиотека общего назначения: хелперы (`Arr`, `Str`, `Json`, `Bit`, `B64`,
`Number`, `URLify`), коллекции, трейты, исключения, типы. 53 файла в `src/`, 4074 NCLOC.

### Текущее состояние (замерено)

| Метрика                      | Значение                       | Комментарий                           |
|------------------------------|--------------------------------|---------------------------------------|
| Тесты                        | **475 зелёных**, 2995 ассертов | 2 PHP deprecation, 4 PHPUnit notice   |
| Покрытие строк               | **81.9%** (831/1015)           | 8 файлов ниже 50%, 9 файлов с 0%      |
| Покрытие методов             | **87.9%** (225/256)            |                                       |
| PHPStan (как настроен)       | **0 ошибок**                   | но level 2 и 3 файла в `excludePaths` |
| PHPStan level 9              | **62 ошибки**                  | без учёта исключённых файлов          |
| PHPCS (PSR-12)               | 0 errors, **4 warnings**       | только длина строк                    |
| `composer validate --strict` | OK                             |                                       |

### Главный вывод

Зелёный CI не отражает реального качества. Настройки инструментов подобраны так, что пропускают ошибки: PHPStan работает
на **level 2** и не видит самый большой класс пакета (`ArrayCollection`, 876 строк); PHPUnit не настроен на
`failOnWarning`/`failOnDeprecation`, поэтому **все** `Warning`/`Deprecated`, перечисленные ниже, проходят мимо CI.

Найдено **65 находок**, из них **5 критичных** — с потерей или порчей данных. Самые серьёзные:

* `Arr::toPostgresArray(['a,b','c'])` возвращает `'{a,b,c}'` — 2 элемента превращаются в 3;
* `json_encode(new ArrayCollection([1,2]))` возвращает `'{}'` — данные исчезают молча;
* `UseConfigurableStorage` не выполняет свою функцию: метод `configureProps()` **не вызывается ниоткуда**.

---

## 2. Методика

Проверялось:

1. Полное чтение `src/` (53 файла), `phpstan.neon`, `phpunit.xml`, `.phpcs.xml`,
   `composer.json`, `.github/workflows/`, `readme.md`, `CHANGELOG.md`, `infection.json`.
2. Разбор `clover.xml` — покрытие по каждому файлу.
3. Прогон инструментов:
   ```bash
   ./vendor/bin/phpunit --no-coverage --no-logging --display-deprecations --display-notices
   ./vendor/bin/phpstan analyse src --level=9 --no-progress
   ./vendor/bin/phpcs -q --report=full
   composer validate --strict && composer outdated --direct
   ```
4. **Каждая находка из разделов 3–5 воспроизведена запуском PHP-кода** на PHP 8.5.9 с реальным автозагрузчиком пакета. В
   отчёте приводится фактический вывод, а не рассуждение.
5. Программная сверка README со списком публичных методов через Reflection.

### Как читать находки

Приоритеты:

* **P0** — потеря или порча данных, молчаливый отказ функции. Чинить в ближайшем патче.
* **P1** — некорректное поведение на краевых входах; ошибки видны как `Warning`/`TypeError`.
* **P2** — пробел бизнес-логики: API делает не то, что обещает имя или docblock.

---

## 3. Критичные баги (P0)

### P0-1 · `Arr::toPostgresArray()` порождает некорректный литерал PG-массива

**Где:** `src/Helpers/Arr.php:204`

```php
return str_replace(['[', ']', '"'], ['{', '}', ''], $json);
```

**Воспроизведение:**

```php
Arr::toPostgresArray(['a,b', 'c']);        // '{a,b,c}'      ← 2 элемента стали 3
Arr::toPostgresArray(['he said "hi"']);    // '{he said \hi\}' ← мусор от JSON-эскейпа
Arr::toPostgresArray(['null', 'x']);       // '{null,x}'     ← строка 'null' станет SQL NULL
```

> **Уточнение (проверено при исправлении).** PostgreSQL читает *неквотированный* `null` в любом регистре как
> SQL NULL, поэтому старый вывод `{null,x}` для PHP `null` был по сути корректен. Реальный дефект здесь
> обратный: строка `'null'` тоже писалась без кавычек и превращалась в NULL.

**Что не так.** Реализация берёт JSON и слепо заменяет скобки и вырезает **все** двойные кавычки. В формате PG-массива
кавычки — не декорация, а границы элемента: они нужны, когда элемент содержит `,`, `{`, `}`, `"`, `\`, пробел или
является пустой строкой. Обратные слэши от JSON-эскейпа (`\"` → `\`) остаются в результате.

**Влияние.** Тихая порча данных при записи в PostgreSQL. Значения с запятой разбиваются на несколько элементов;
строка `'null'` превращается в SQL NULL; строки с кавычками ломают литерал. Ошибка не проявляется до чтения данных обратно.

**Исправление.** Формировать литерал элемент за элементом:
`NULL` → `NULL`; строку экранировать `\` и `"`, оборачивать в `"` всегда (или по условию наличия спецсимволов);
вложенные массивы — рекурсивно. Не использовать JSON как промежуточный формат. Добавить тест-раундтрип
`toPostgresArray()` → `fromPostgresArray()`.

---

### P0-2 · `ArrayCollection` не сериализуется в JSON — данные исчезают

**Где:** `src/Structures/Collections/ArrayCollection.php:45`

```php
class ArrayCollection implements Collection, Stringable
```

**Воспроизведение:**

```php
json_encode(new ArrayCollection([1, 2]));   // '{}'
```

**Что не так.** Класс реализует `Collection`, `Stringable`, `Countable`, `IteratorAggregate`,
`ArrayAccess`, но **не** `JsonSerializable` и **не** `Php\Support\Interfaces\Arrayable` — хотя метод `toArray()` у него
есть. `json_encode()` для объекта без `JsonSerializable` сериализует публичные свойства, а `$elements` объявлен
`protected` → пустой объект.

**Влияние.** Любой код, отдающий коллекцию в HTTP-ответ, лог или очередь через `json_encode()`, молча теряет все данные.
Ни исключения, ни warning'а.

**Исправление.** `implements Collection, Stringable, JsonSerializable, Arrayable` +
`public function jsonSerialize(): array { return $this->elements; }`. То же — для `HashCollection`. Добавить тест
`assertSame('[1,2]', json_encode($collection))`.

---

### P0-3 · `UseConfigurableStorage` не работает: `configureProps()` — мёртвый код

**Где:** `src/Traits/UseConfigurableStorage.php:22`

```php
protected function configureProps(string $key, mixed $value): bool
{
    $this->set($key, $value);
    return true;
}
```

**Воспроизведение:**

```php
class Cfg { use UseConfigurableStorage; public ?string $known = null; }

(new Cfg)->configurable(['unknown' => 'v']);
// InvalidParamException: Property unknown is absent at class: Cfg

in_array('configureProps', get_class_methods(Cfg::class), true);  // false — protected, не вызывается
```

Покрытие подтверждает: `src/Traits/UseConfigurableStorage.php` — **0/2 строк, 0/1 методов**.

**Что не так.** Цепочка вызовов в `ConfigurableTrait::applyValue()`
(`src/Traits/ConfigurableTrait.php:27`) выглядит так:

```php
return $this->callSetterProp($key, $value) || $this->setPropValue($key, $value);
```

`configureProps()` в ней отсутствует. Ни один код в пакете его не зовёт. Смысл трейта — «конфигурировать объект,
складывая неизвестные ключи в `Storage`» — не реализован: неизвестный ключ либо бросает исключение, либо (при
`$throwOnMissingProp = false`) молча отбрасывается.

**Влияние.** Заявленная функциональность трейта отсутствует. Существующий тест
`UseConfigurableStorageTest::testConfigurableThrowsForUnknownKey` **закрепляет баг как ожидаемое поведение**, поэтому
дефект не всплывал.

**Исправление.** Переопределить в `UseConfigurableStorage`:

```php
protected function applyValue(string $key, mixed $value): bool
{
    return parent::applyValue($key, $value) || $this->configureProps($key, $value);
}
```

(в трейтах — через `insteadof`/алиасинг `ConfigurableTrait::applyValue`). Переписать тесты: неизвестный ключ должен
попадать в `Storage`, а не бросать исключение.

---

### P0-4 · `UseStorage`: неинициализированное typed-свойство даёт фатальную ошибку

**Где:** `src/Traits/UseStorage.php:28`

```php
protected function propertyExists(string $name): bool
{
    return $name !== 'storage' && property_exists($this, $name);
}
```

**Воспроизведение:**

```php
class S1 { use UseStorage; public string $name = 'x'; }

$o = new S1;
unset($o->name);   // публичное свойство — __unset() НЕ вызывается, свойство становится uninitialized
$o->name;          // Error: Typed property S1::$name must not be accessed before initialization
```

**Что не так.** `property_exists()` возвращает `true` и для объявленного, но неинициализированного typed-свойства.
`get()` идёт по ветке «свойство есть» и читает неинициализированную память вместо фолбэка в `Storage`. Симметричная
проблема в
`__unset()` (`UseStorage.php:68`): для объявленного свойства выполняется `$this->$name = null`, что для non-nullable
типа даёт `TypeError`.

**Влияние.** Фатальная ошибка вместо graceful-фолбэка. Трейт продаёт «прозрачный доступ свойство-или-хранилище», а на
практике ломается на штатном `unset()`.

**Исправление.** Различать «объявлено» и «инициализировано»:

```php
protected function propertyExists(string $name): bool
{
    return $name !== 'storage'
        && property_exists($this, $name)
        && (new \ReflectionProperty($this, $name))->isInitialized($this);
}
```

(с кэшированием рефлексии). В `__unset()` для объявленного свойства использовать
`unset($this->$name)`, а не присваивание `null`.

---

### P0-5 · `Arr::fromPostgresPoint()` возвращает «валидную» точку из мусора

**Где:** `src/Helpers/Arr.php:348`

```php
[$x, $y] = explode(',', $string);
return [(float)$x, (float)$y];
```

**Воспроизведение:**

```php
Arr::fromPostgresPoint('garbage');
// Warning: Undefined array key 1 in Arr.php on line 360
// => [0.0, 0.0]

Arr::fromPostgresPoint('(1,2,3)');
// => [1.0, 2.0]   ← третья координата молча отброшена
```

**Что не так.** Нет ни проверки формата `(x,y)`, ни количества частей после `explode()`.
`mb_substr($value, 1, -1)` слепо отрезает первый и последний символ, предполагая скобки.

**Влияние.** Испорченная запись в БД или чужой формат превращаются в точку `(0, 0)` вместо ошибки — координаты «уезжают»
в нулевой меридиан. Симметричный `toPostgresPoint()`
(`Arr.php:217`) корректно возвращает `null` при `count !== 2`, читатель — нет.

**Исправление.** Валидировать регуляркой `/^\(\s*(-?[\d.eE+]+)\s*,\s*(-?[\d.eE+]+)\s*\)$/`, возвращать `null` при
несовпадении. Тот же фикс защитит `Point::castFromDatabase()`
(`src/Types/Point.php:91`), который на этот метод опирается.

---

## 4. Существенные дефекты (P1)

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

#### P1-2 · `slugifyWithFormat()` — regex-инъекция через параметр `$format`

**Где:** `src/Helpers/Str.php:316`

```php
$slug = preg_replace("/$format/", $separator, mb_strtolower(self::removeAccents($str)));
```

**Воспроизведение:**

```php
Str::slugifyWithFormat('a/b', '-', 'a/b');
// Warning: preg_replace(): Unknown modifier 'b'
// => ''
```

**Что не так.** `$format` вставляется в шаблон между разделителями `/…/` без экранирования. Любой `/` в аргументе рвёт
шаблон; при неудаче `preg_replace()` возвращает `null`, и функция отдаёт `''` вместо осмысленной ошибки. Если `$format`
приходит из конфига или пользовательского ввода — это точка отказа (в худшем случае — ReDoS через вложенные
квантификаторы).

**Исправление.** Принимать `$format` уже как готовый шаблон с делимитерами и валидировать через
`Str::isRegExp()`, либо использовать делимитер `#` c `preg_quote($format, '#')` для литеральной трактовки. Проверять
результат `preg_replace() === null` и бросать `InvalidParamException`.

---

#### P1-3 · `truncate()` не валидирует длину

**Где:** `src/Helpers/Str.php:279`

```php
Str::truncate('abc', 0);    // '...'      ← только суффикс
Str::truncate('abc', -1);   // 'ab...'    ← отрицательная длина = «отрезать с конца»
```

**Что не так.** `mb_substr($str, 0, -1)` трактует отрицательную длину как «всё, кроме последнего символа». Отрицательный
аргумент проходит молча и даёт бессмысленный результат.

**Исправление.** `$length < 1` → бросать `InvalidParamException` (или возвращать `''`). Дополнительно: учитывать длину
`$append` в бюджете (сейчас `truncate('abcdef', 3)` даёт 6 символов).

---

#### P1-4 · Кэши `Str` растут неограниченно

**Где:** `src/Helpers/Str.php:21` (`$delimitedCache`), `Str.php:28` (`$camelCache`)

**Воспроизведение:**

```php
for ($i = 0; $i < 5000; $i++) { Str::toSnake("SomeValue$i"); }
// delimitedCache entries: 5000
```

**Влияние.** В долгоживущих процессах (RoadRunner, Swoole, Octane, воркеры очередей) статические кэши никогда не
очищаются. При конвертации пользовательских строк (имена файлов, заголовки, ключи из API) это утечка памяти, растущая
линейно по количеству уникальных входов.

**Исправление.** LRU с жёстким лимитом (например 1000 записей) либо публичный
`Str::clearCache(): void` + документированное предупреждение. Как вариант — кэшировать только строки короче N символов.

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

#### P1-6 · `Bit::toInt()` молча проглатывает нечисловую строку

**Где:** `src/Helpers/Bit.php:48`

```php
Bit::addFlag('abc', 1);
// Deprecated: Invalid characters passed for attempted conversion, these have been ignored
// => 1     ← 'abc' превратилось в 0
```

**Что не так.** `bindec()` игнорирует любые символы, кроме `0` и `1`. `'abc'` → `0`, и итог маски вычисляется от нуля. С
PHP 8.3 это ещё и `Deprecated`, который в CI не фейлит.

**Исправление.** Валидировать `preg_match('/^[01]+$/', $value)` и бросать
`InvalidParamException`. Аналогично в `removeFlag()`, `checkFlag()`.

---

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

#### P1-9 · `removeByValue()` использует нестрогое сравнение без возможности его отключить

**Где:** `src/Helpers/Arr.php:75`

```php
$key = array_search($val, $array, false);
```

```php
$a = ['1', '2'];
Arr::removeByValue($a, 1);   // вернул 0, удалил '1'
```

**Что не так.** Docblock упоминает только регистрозависимость, о нестрогости сравнения не сказано. Параметра `$strict`
нет.

**Исправление.** Добавить `bool $strict = true` (в 6.0 — сделать `true` по умолчанию; в 5.x — `false` для совместимости,
но задокументировать).

---

#### P1-10 · `fillKeysByValues()` молча выдаёт `null` для ассоциативных ключей

**Где:** `src/Helpers/Arr.php:605`

```php
Arr::fillKeysByValues(['k1' => 'a', 'k2' => 'b'], ['x', 'y']);
// ['a' => null, 'b' => null]
```

**Что не так.** Метод берёт `$values[$key]`, где `$key` — ключ из `$keys`. Для списка (`0,1,2…`)
это работает, для ассоциативного массива — ищет `$values['k1']`, которого нет, и подставляет `null`.

**Влияние.** Тихая потеря всех значений. Ошибка обнаруживается только по итоговым `null`.

**Исправление.** Использовать `array_values($keys)` для позиционного сопоставления, либо бросать исключение при
`!array_is_list($keys)`.

---

#### P1-11 · `merge()`: `null` в целочисленном ключе ломает логику добавления

**Где:** `src/Helpers/Arr.php:180`

```php
Arr::merge([0 => null], [0 => 'x']);   // [0 => 'x']  ожидалось [null, 'x']
```

**Что не так.** Проверка `isset($res[$key])` считает существующий `null` отсутствующим, поэтому вместо добавления в
конец происходит перезапись. Для строковых ключей используется другой механизм — поведение несимметрично.

**Исправление.** Заменить на `array_key_exists($key, $res)`.

---

#### P1-12 · `fromPostgresArrayWithBraces()` теряет пустую строку

**Где:** `src/Helpers/Arr.php:286`

```php
Arr::fromPostgresArray('{""}');   // []   ожидалось ['']
```

**Что не так.** Условие `if ($v !== '' || !empty($return))` отбрасывает единственный пустой элемент, поскольку парсер не
различает «пустое значение» и «значения не было». Флаг `$string`
(были ли кавычки) в решение не входит.

**Исправление.** Отслеживать факт входа в элемент отдельным флагом и не использовать пустоту буфера как признак
отсутствия.

---

### Типы `Point` / `GeoPoint`

#### P1-13 · `Point::fromJson()` падает на JSON без ключей `x`/`y`

**Где:** `src/Types/Point.php:75`

```php
Point::fromJson('{"a":1}');
// Warning: Undefined array key "x"
// Warning: Undefined array key "y"
// TypeError: Point::__construct(): Argument #1 ($x) must be of type float, null given
```

**Что не так.** Нет проверки структуры декодированного массива. Метод объявлен как
`?Jsonable` — контракт предполагает `null` при неудаче, а не `TypeError`. То же в `GeoPoint::fromJson()`
(`src/Types/GeoPoint.php:49`) для ключей `longitude`/`latitude`.

**Исправление.** Проверять `isset($array['x'], $array['y'])` и возвращать `null` (или бросать
`InvalidParamException` — но единообразно с `fromArray()`, которая бросает).

---

#### P1-14 · `GeoPoint::toJson(): string` может вернуть `null`

**Где:** `src/Types/GeoPoint.php:26`

```php
public function toJson($options = 320): string   // а Json::encode() возвращает ?string
```

**Что не так.** Родительский `Point::toJson()` корректно объявлен `?string`. У наследника тип сужен до `string`, при
этом `Json::encode()` возвращает `null` на любой ошибке кодирования (например `INF`/`NAN` в координатах) → `TypeError` в
рантайме. Дополнительно: docblock ссылается на `@throws \Php\Support\Exceptions\JsonException` — **такого класса в
пакете нет** (проверено `class_exists()`).

**Исправление.** Привести к `?string`; убрать несуществующий `@throws`.

---

### Коллекции

#### P1-15 · `getProperty($throwOnMiss = true)` не бросает исключение для массивов

**Где:** `src/Structures/Collections/ArrayCollection.php:288`

```php
(new ArrayCollection([['a' => 1]]))->mapByKey('nope');
// Warning: Undefined array key "nope"
// Deprecated: Using null as an array offset is deprecated
// => ['' => ['a' => 1]]
```

**Что не так.** Ветка `is_array($target) || $target instanceof \ArrayAccess` при
`$throwOnMiss = true` делает `$target[$keyName]` — для массива это `Warning` + `null`, а не исключение. Дальше `null`
уходит ключом результата → `Deprecated` + ключ `''`. Исключение бросается только в `default`-ветке (скаляры).

**Влияние.** Опечатка в имени поля даёт молча схлопнутую коллекцию (все элементы под ключом `''`)
вместо явной ошибки.

**Исправление.** Для массивов при `$throwOnMiss = true` проверять
`array_key_exists()`/`offsetExists()` и бросать `MissingPropertyException`.

---

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

### Трейты

#### P1-16 · `getCallbackActions('0')` возвращает все группы

**Где:** `src/Traits/HasPrePostActions.php:21`

```php
return $key ? $this->executeCallbacks[$key] ?? [] : $this->executeCallbacks;
```

Строка `'0'` в PHP ложна, поэтому группа с именем `'0'` трактуется как «ключ не передан».

**Воспроизведение:** объект с группой `other` → `getCallbackActions('0')` вернул 1 элемент (всю карту групп) вместо
пустого массива.

**Влияние.** `runActions('0', …)` выполнит колбэки **всех** групп. Числовые имена групп — не экзотика (стадии,
приоритеты).

**Исправление.** `$key !== null ? … : …`.

---

#### P1-17 · `ConsolePrint` падает вне CLI

**Где:** `src/Traits/ConsolePrint.php:15,23`

```php
fwrite(STDOUT, …);   fwrite(STDERR, …);
```

**Что не так.** Константы `STDOUT`/`STDERR` определены только в CLI/phpdbg SAPI. Под
`php-fpm`, `apache2handler`, RoadRunner/Swoole их нет → `Error: Undefined constant "STDOUT"`.

**Исправление.** `fwrite(fopen('php://stdout', 'wb'), …)` (или `php://stderr`), с ленивой инициализацией дескриптора.

---

#### P1-19 · `TestingHelper` использует метод, deprecated с PHP 8.5

**Где:** `src/Testing/TestingHelper.php:27,46`

```php
$methodReflex->setAccessible(true);
$property->setAccessible(true);
```

```
Deprecated: Method ReflectionMethod::setAccessible() is deprecated since 8.5,
as it has no effect since PHP 8.1
```

**Влияние.** CI пакета гоняет PHP 8.5. Deprecation попадает в проекты потребителей. Та же проблема в тестах пакета —
`tests/Helpers/HasReflection.php:16`.

**Исправление.** Удалить вызовы — с PHP 8.1 они не нужны.

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

## 5. Пробелы бизнес-логики (P2)

### P2-1 · Нет общего интерфейса-маркера для исключений пакета

**Где:** `src/Exceptions/` (15 классов)

Фактическая иерархия:

| Класс                                                                                                                                                                                                                     | Базовый                            |
|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|------------------------------------|
| `Exception`, `ConfigException`, `InvalidConfigException`, `MissingConfigException`, `MissingClassException`, `MissingPropertyException`, `MethodNotAllowedException`, `NotSupportedException`, `UnknownPropertyException` | `Php\Support\Exceptions\Exception` |
| `InvalidArgumentException`                                                                                                                                                                                                | `\InvalidArgumentException`        |
| `InvalidCallException`, `MissingMethodException`, `UnknownMethodException`                                                                                                                                                | `\BadMethodCallException`          |
| `InvalidParamException`                                                                                                                                                                                                   | `\LogicException`                  |
| `InvalidValueException`                                                                                                                                                                                                   | `\UnexpectedValueException`        |

`interface_exists('Php\Support\Exceptions\ExceptionInterface')` → `false`.

**Влияние.** Потребитель не может перехватить «любую ошибку этого пакета» одним `catch`. Приходится либо ловить
`\Throwable`, либо перечислять 15 классов. Для библиотеки это базовый недочёт API — общепринятая практика (PSR, Symfony,
Doctrine) требует маркер-интерфейса. Дополнительно `Maker`/`Thrower` подключены непоследовательно: `Maker` — только в
`Exception`,
`Thrower` — в `Exception` и `MissingMethodException`, у остальных 13 классов `::throw()` нет.

**Исправление.** Ввести `interface ExceptionInterface extends \Throwable {}` и реализовать его во всех 15 классах (не
breaking — только добавление). `Thrower` подключить единообразно.

---

### P2-2 · `Arr::remove()` игнорирует разделитель

**Где:** `src/Helpers/Arr.php:513` против `Arr::get()` (`:377`), `Arr::set()` (`:482`),
`Arr::has()` (`:443`)

```php
Arr::has(['a' => ['b' => 1]], 'a/b', '/');       // true — разделитель учтён
$a = ['a' => ['b' => 1]];
Arr::remove($a, 'a/b');                          // ничего не удалено — внутри жёстко '.'
```

`Arr::remove()` — единственный из четвёрки без параметра `$separator`; внутри `explode('.', $key)`
захардкожен (`Arr.php:530`).

**Влияние.** Асимметричный API. `Storage::remove()` (`src/Storage.php:38`) наследует проблему:
записать по кастомному разделителю можно, удалить — нет.

**Исправление.** Добавить `string $separator = '.'` четвёртым параметром (не breaking).

---

### P2-3 · `Arr::set()` возвращает вложенный подмассив вместо всего массива

**Где:** `src/Helpers/Arr.php:482`

```php
$a = [];
$ret = Arr::set($a, 'a.b', 1);
// $ret => ['b' => 1]           ← подмассив
// $a   => ['a' => ['b' => 1]]  ← сам массив изменён корректно
```

**Что не так.** Цикл переприсваивает `$array = &$array[$key]`, поэтому к моменту `return`
переменная указывает на самый глубокий уровень. Docblock обещает
`@return T[]|array<TKey,T>|ArrayObject<TKey,T>` — то есть весь контейнер.

**Влияние.** `$result = Arr::set($config, 'db.host', 'x');` даёт `['host' => 'x']` — легко принять за весь конфиг.
Возвращаемое значение бесполезно и вводит в заблуждение.

**Исправление.** Сохранить `$original = &$array` в начале и возвращать его (как уже сделано в
`Arr::remove()`), либо изменить сигнатуру на `void` (breaking).

---

### P2-4 · `Storage`: не итерируем, `count()` неполный, ключ с точкой молча вкладывается

**Где:** `src/Storage.php:20,33,93`

```php
class_implements(Storage::class);
// ArrayAccess, Countable, JsonSerializable, Stringable  ← нет IteratorAggregate

$s = new Storage();
$s->set('a.b', 1);
$s->data;     // ['a' => ['b' => 1]]   ← ключ 'a.b' невозможен
$s->set('a.c', 2);
count($s);    // 1   ← считается только верхний уровень
```

**Что не так.**

1. `foreach ($storage as $k => $v)` невозможен, хотя это ожидаемая операция для хранилища.
2. Все ключи проходят через `Arr::set()` с точечной нотацией — сохранить ключ, содержащий `.`
   (например `user.email` как плоский ключ или `app.php` как имя файла) нельзя.
3. `count()` возвращает число ключей верхнего уровня, что расходится с числом реально записанных значений.
4. Нет `all()` / `toArray()` и реализации `Arrayable` — единственный доступ к данным через публичное `private(set)`
   свойство `$data` или `jsonSerialize()`.

**Исправление.** Добавить `IteratorAggregate` + `Arrayable::toArray()`; ввести
`bool $dotNotation = true` в конструктор или `?string $separator` в `set/get/remove`; задокументировать семантику
`count()` (или добавить `countRecursive()`).

---

### P2-5 · `HashCollection` — не коллекция по возможностям

**Где:** `src/Structures/Collections/HashCollection.php:19`

Реализует только `ArrayAccess` и `Countable`. Нет `IteratorAggregate` → `foreach` невозможен. Нет `map`, `filter`,
`each`, `keys`, `values`, `reduce`, `first`, `last`, `merge` — при том, что у соседнего `ArrayCollection` они есть.
Единственный «функциональный» метод — `find()`
(`HashCollection.php:195`), причём с обратным порядком аргументов колбэка (`$key, $element`)
относительно `ArrayCollection::findFirst()` (тоже `$key, $element`, но у `map`/`filter` —
`$value, $key`).

**Влияние.** Класс подан в README и CHANGELOG как «Collection», но не поддерживает базовые операции. Пользователь
вынужден делать `$hc->all()` и работать с сырым массивом.

**Исправление.** Реализовать `IteratorAggregate`, `JsonSerializable`, `Arrayable`; добавить
`map`/`filter`/`each`/`keys`/`values`. Унифицировать порядок аргументов колбэков во всех коллекциях (сейчас в
`ArrayCollection` он сам по себе неконсистентен: `exists`/`partition`/
`testForAll`/`findFirst` принимают `($key, $element)`, а `filter`/`map`/`each`/`reject` —
`($value, $key)`).

---

### P2-6 · `ArrayCollection`: `new static()` вместо `createFrom()` ломает наследников

**Где:** `src/Structures/Collections/ArrayCollection.php:784,787` (`random()`)

```php
return new static(Arr::random($this->elements, $number, $preserveKeys));
```

Во всём остальном классе для создания производных коллекций используется
`protected function createFrom()` (`ArrayCollection.php:387`) — специально задокументированная точка расширения «для
наследников с изменённой семантикой конструктора». `random()` её обходит.

**Влияние.** Наследник с обязательными параметрами конструктора получает `ArgumentCountError`
при вызове `random()`, хотя `map()`, `filter()`, `chunk()` у него работают.

Рядом: `chunk(0)` (`ArrayCollection.php:611`) молча возвращает пустую коллекцию вместо
`InvalidParamException` — невалидный аргумент подан как валидный результат.

**Исправление.** `random()` → `$this->createFrom(...)`; `chunk()` при `$size <= 0` бросать
`InvalidParamException`.

---

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

### P2-9 · `UseErrorsBox::setError()` называется не тем, что делает

**Где:** `src/Traits/UseErrorsBox.php:14`

```php
public function setError(string|\Throwable $message): static
{
    $this->errors[] = (string)$message;   // добавляет, а не устанавливает
```

**Что не так.** `set*` в PHP-конвенциях означает замену. Метод добавляет. Также отсутствуют:
`firstError()`, `lastError()`, коды/ключи ошибок, слияние коробок, `errorsCount()`.
`\Throwable` теряет всё, кроме `getMessage()` — код, класс, previous, стек.

**Исправление.** Ввести `addError()`, `setError()` оставить как `@deprecated` алиас (удалить в 6.0). Добавить
`firstError()` и опциональный ключ:
`addError(string|\Throwable $e, ?string $key = null)`.

---

### P2-10 · `Json`: ошибка кодирования/декодирования теряется без следа

**Где:** `src/Helpers/Json.php:44` (`encode`), `Json.php:63` (`decode`)

```php
Json::encode(NAN);              // null   — почему? неизвестно
Json::encode(['r' => STDIN]);   // null   — почему? неизвестно
Json::decode('null');           // null   ← валидный JSON
Json::decode('{bad');           // null   ← битый JSON — не отличить
```

**Что не так.** Оба метода возвращают `null` при любой проблеме и не дают способа узнать причину: нет
`JSON_THROW_ON_ERROR`, нет `json_last_error_msg()`, нет вариантов `*OrThrow`.
`decode()` дополнительно смешивает «валидный `null`» и «ошибку разбора».

**Влияние.** Тихие сбои в местах, где JSON — контракт с внешней системой.
`Arr::toArray()` (`Arr.php:110`) и `Arr::dataToArray()` (`Arr.php:136`) опираются на
`Json::decode()` и наследуют неоднозначность.

**Исправление.** Добавить `Json::decodeOrThrow()` / `Json::encodeOrThrow()`, бросающие
`InvalidValueException` с `json_last_error_msg()`. Существующие методы оставить как есть (обратная совместимость), но
задокументировать поведение.

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
| `src/Exceptions/MethodNotAllowedException.php`       | `$this->reason = $reason;` дублирует promoted-свойство; `$message ? … : $reason` при непустом дефолте — мёртвая ветка                                               |
| `src/Exceptions/MissingMethodException.php:15-20`    | Property hook `get { return $this->method; }` эквивалентен обычному свойству — лишний шум, к тому же отформатирован с нарушением стиля                              |
| `src/Exceptions/MissingConfigException.php:14`       | `protected ?string $needKey` нигде не используется и не попадает в сообщение                                                                                        |
| `src/Helpers/Str.php:263`                            | `replaceByTemplate(string $str, …): array\|string` — вход `string`, `str_replace()` всегда вернёт `string`. Тип возврата излишне широк                              |
| `src/Helpers/Bit.php:79`                             | `exist(array $list, int $bit)` — тривиальная обёртка `checkFlag(grant($list), $bit)` со сломанным неймингом                                                         |
| `src/Helpers/Str.php:356`                            | `seemsUTF8()` — чистое проксирование в `URLify::seemsUTF8()`                                                                                                        |
| `src/Helpers/Arr.php:645`                            | `Arr::random()` бросает глобальный `\InvalidArgumentException`, а не `Php\Support\Exceptions\InvalidArgumentException` — вываливается из иерархии пакета            |
| `src/Structures/Collections/ArrayCollection.php:296` | `throw new \Php\Support\Exceptions\InvalidParamException(...)` — FQN инлайном вместо `use`                                                                          |
| `src/Global/base.php:61`                             | `dataGet()` использует `isset($target->{$segment})` — публичное свойство со значением `null` даёт `$default` вместо `null`                                          |

---

## 6. Типизация и статический анализ

### P2-14 · Конфигурация PHPStan скрывает проблемы

**Где:** `phpstan.neon`

```neon
parameters:
  level: '2'
  paths: [src]
#    - tests
  excludePaths:
    - 'src/Types/Point.php'
    - 'src/Types/GeoPoint.php'
    - 'src/Structures/Collections/ArrayCollection.php'
```

**Проблемы:**

1. **Level 2** — очень низкий для библиотеки на PHP 8.4 с активным использованием дженериков.
2. **`ArrayCollection.php` (876 строк, крупнейший класс пакета) исключён из анализа целиком** — вместе с ним не
   проверяются `Point`/`GeoPoint`, где как раз найдены P1-13 и P1-14.
3. `tests` закомментированы — тесты не типизируются.
4. Замеры по уровням (без исключённых файлов): level 5 → 22, level 6 → 23, level 8 → 37, level 9 → **62 ошибки**.

**Исправление.** Поэтапно: снять `excludePaths` → зафиксировать level 6 → включить `tests`. Для переходного периода
использовать `phpstan-baseline.neon`, а не исключение файлов.

---

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

### P2-16 · Смесь `@psalm-*` и `@phpstan-*` при отсутствии Psalm

`ArrayCollection.php` использует `@psalm-template`, `@psalm-param`, `@psalm-return`,
`@psalm-consistent-constructor`, `@psalm-suppress`. `Collection.php`/`ReadableCollection.php` —
`@phpstan-*`. Psalm в `require-dev` отсутствует → `@psalm-*` не читается никем (PHPStan понимает часть, но не все).

**Исправление.** Либо добавить Psalm в CI, либо перевести `ArrayCollection` на `@phpstan-*`.

---

### P2-17 · `declare(strict_types=1)` отсутствует в 2 файлах

`src/Global/base.php` и `src/Traits/ConfigurableTrait.php` — без директивы; остальные 51 файл — с ней. В `base.php` это
особенно значимо: глобальные функции с нестрогой типизацией молча приводят типы.

---

## 7. Тесты и качество

### Покрытие по файлам (из `clover.xml`)

|  Покрытие | Файл                                                                                                                                                                  | Комментарий                                      |
|----------:|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------|
|    **0%** | `src/Traits/UseConfigurableStorage.php`                                                                                                                               | подтверждает P0-3 — метод не вызывается          |
|    **0%** | `src/Testing/AdditionalAssertionsTrait.php` (0/26)                                                                                                                    |                                                  |
|    **0%** | `src/Testing/TestingHelper.php` (0/7)                                                                                                                                 |                                                  |
|    **0%** | `Exceptions/InvalidCallException`, `InvalidConfigException`, `InvalidValueException`, `MethodNotAllowedException`, `MissingConfigException`, `MissingMethodException` | 6 классов исключений без единого теста           |
|  **9.3%** | `src/Helpers/URLify.php` (5/54)                                                                                                                                       | 794 строки, покрыт только `downcode` по латинице |
|   **40%** | `src/Traits/Whener.php`                                                                                                                                               | единственный тест — `when(true, fn() => 1)`      |
| **47.9%** | `src/Global/base.php` (45/94)                                                                                                                                         | половина глобальных функций не тестируется       |
| **85.7%** | `src/Traits/Singleton.php`                                                                                                                                            |                                                  |
| **87.2%** | `src/Helpers/Arr.php` (184/211)                                                                                                                                       | непокрыты как раз краевые ветки из P1-9…P1-12    |
| **87.5%** | `src/Storage.php`                                                                                                                                                     |                                                  |
| **90.0%** | `src/Traits/UseStorage.php`                                                                                                                                           |                                                  |

### P2-18 · PHPUnit не фейлится на Warning/Deprecation/Notice

**Где:** `phpunit.xml`

Отсутствуют `failOnWarning`, `failOnDeprecation`, `failOnNotice`, `failOnRisky`,
`beStrictAboutOutputDuringTests`. Как следствие:

* **все** `Warning`, найденные в разделах 3–4, проходят мимо CI;
* текущий прогон даёт `OK, but there were issues!` — 2 deprecation, 4 PHPUnit notice — и считается зелёным;
* `ConsolePrintTest` печатает в STDOUT прямо в отчёт (`Test message`, дампы `print_r`).

**Исправление:**

```xml

<phpunit ... failOnWarning="true" failOnDeprecation="true" failOnNotice="true"
        failOnRisky="true" beStrictAboutOutputDuringTests="true">
```

Вводить поэтапно: сначала `failOnWarning`, после исправления P1 — остальные.

### P2-19 · Прочее по тестам

* `xsi:noNamespaceSchemaLocation` указывает на схему **12.3**, установлен PHPUnit **12.5**.
* 4 notice `No expectations were configured for the mock object` — вместо `createMock()`
  для `Arrayable`/`Jsonable` нужен `createStub()`.
* `tests/Helpers/HasReflection.php:16` — deprecated `setAccessible()` (см. P1-19).
* `.phpunit.result.cache` расползается по `src/`, `tests/`, `tests/Helpers/`, `tests/Traits/` — относительный
  `cacheDirectory` резолвится от CWD.
* Нет тестов на: мультибайтовые входы `Str`, PG-массивы с кавычками/запятыми/NULL, наследование коллекций (`createFrom`в
  наследнике), не-CLI-режим `ConsolePrint`.

---

## 8. Инфраструктура

### P2-20 · Не объявлены обязательные расширения

**Где:** `composer.json`

```json
"require": {
  "php": "^8.4",
  "ext-mbstring": "*"
}
```

Не объявлены:

* **`ext-json`** — весь `Helpers/Json.php` (`json_encode`, `json_decode`, `json_validate`), а через него
  `Arr::toArray()`, `Arr::dataToArray()`, `Arr::toPostgresArray()`, `Storage::__toString()`,
  `Point::toJson()`, `GeoPoint::toJson()`.
* **`ext-ctype`** — `ctype_digit()` в `Arr::fromPostgresArrayWithBraces()` (`Arr.php:320`).

Оба обычно скомпилированы в PHP, но не гарантированно (`--disable-json` невозможен с 8.0, а вот `--disable-ctype` —
вполне). Composer не сможет предупредить об отсутствии.

**Исправление.** Добавить `"ext-json": "*"`, `"ext-ctype": "*"` в `require`.

---

### P2-21 · Патч-релизы не создают GitHub Release

**Где:** `.github/workflows/release.yml`

```yaml
on:
  push:
    tags:
      - 'v[1-9].[0-9]+.0'
```

Шаблон совпадает только с тегами вида `vX.Y.0`. Теги `v5.2.1`, `v5.1.3` (реально существующий в CHANGELOG) release не
создают. Также не покрыты `v10.x` (шаблон `[1-9]` — одна цифра) и pre-release-теги.

**Исправление.** `- 'v[0-9]+.[0-9]+.[0-9]+'` (при желании — плюс `-'v[0-9]+.[0-9]+.[0-9]+-*'`).

---

### P2-22 · CI не тестирует pull request'ы

**Где:** `.github/workflows/php.yml`

```yaml
on: [ push ]
```

PR из форков не запускают workflow — сторонний вклад попадает в мастер непроверенным.

**Прочее по CI:**

* Джоб **PHPCS закомментирован** целиком (строки 5–12) — стиль не проверяется в CI, хотя
  `.phpcs.xml` настроен и `composer phpcs` существует.
* `XDEBUG_MODE=coverage composer test` — но `composer test` → `phpunit --no-coverage`. Xdebug включён впустую
  (замедление ~2–5×), покрытие не собирается и никуда не грузится, при этом в README висят бейджи codecov/CodeClimate.
* Нет кэширования результатов PHPStan.
* `composer validate` без `--strict`.

**Исправление.** `on: [push, pull_request]`; отдельные джобы `phpcs` и `phpstan`; либо убрать `XDEBUG_MODE`, либо
перевести на `composer test-cover` + upload в codecov.

---

### P2-23 · Сломанные и лишние зависимости

* **`composer infection`** ссылается на `./vendor/bin/infection`, но `infection/infection`
  отсутствует в `require-dev` → скрипт падает. Конфиг `infection.json` при этом лежит в репозитории.
* **`symfony/var-dumper`** в `require-dev` не используется (в `src/` и `tests/` — ни одного
  `dump()`/`dd()`, только закомментированный `var_dump` в `ArrTest.php:1308`).
* **Устаревшие версии** (`composer outdated --direct`): `phpstan 2.2.1 → 2.2.9`,
  `php_codesniffer 4.0.1 → 4.0.4`, `symfony/var-dumper 8.1.0 → 8.1.5`, `phpunit 12.5 → 13.3`.
* Нет `dependabot.yml` / Renovate.

---

### P2-24 · PHPCS: 4 предупреждения

```
src/Exceptions/MissingConfigException.php:14   122 символа
src/Helpers/Arr.php:377                        121 символ
src/Structures/Collections/ArrayCollection.php:682  155 символов
src/Structures/Collections/ArrayCollection.php:720  124 символа
```

Все — превышение лимита 120. Плюс `.phpcs.xml` содержит `<exclude-pattern>tests/bootstrap.php`
для файла, которого не существует, и исключает `tests/` из проверки целиком.

---

## 9. Документация

### P2-25 · README

**Точное.** Списки методов `Arr`, `Str`, `Json`, `Bit`, `B64`, `Number` и глобальных функций сверены программно через
Reflection — расхождений нет (единственная незадокументированная глобальная функция — намеренно скрытый
`remoteStaticCallOrTrow`).

**Неточное / отсутствующее:**

| Проблема                      | Детали                                                                                                                                                                                    |
|-------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Инструкция установки устарела | `composer require efureev/support "^5.1"` при актуальной 5.2.0                                                                                                                            |
| Мёртвые бейджи                | Codacy (`api.codacy.com` — legacy API), CodeClimate (`api.codeclimate.com` — сервис Quality свёрнут), codecov на ветку **`v2`**, которой в репозитории нет                                |
| Не задокументировано          | `Php\Support\Testing\*` (2 трейта), `Helpers\URLify`, API `Storage`, API `HashCollection` (только упоминание), полный список методов `ArrayCollection` (~50 методов — в README ни одного) |
| Нет примеров                  | Кроме блока `@example` в `ConditionalHandler` — примеров использования нет вовсе                                                                                                          |
| Нет раздела совместимости     | Не описано, что происходит при установке рядом с Laravel (см. P2-11)                                                                                                                      |

### P2-26 · CHANGELOG

* Нет секции `## [Unreleased]` (правило `CHANGELOG-RULE-001` её допускает).
* Нет ссылок сравнения версий (`[5.2.0]: https://github.com/.../compare/v5.1.3...v5.2.0`) — расхождение с Keep a
  Changelog, на который ссылается сам файл.
* Нет дат релизов, хотя формат `## vX.Y.Z - YYYY-MM-DD` линтером разрешён.
* `.github/workflows/lint/rules/changelog.js:16` — регулярка дат `20[12][0-9]` перестанет работать с 2030 года.

---

## 10. Чего не хватает

### 10.1 API — хелперы

**`Arr`** (есть 23 метода, нет базового набора):
`only()`, `except()`, `pluck()`, `first()`, `last()`, `flatten()`, `wrap()`, `dot()`, `undot()`,
`keyBy()`, `where()`, `isAssoc()`, `isList()`, `sortRecursive()`, `divide()`, `crossJoin()`,
`shuffle()`, `whereNotNull()`. Плюс к существующим: `removeByValue(bool $strict)` (P1-9), `remove(string $separator)`
(P2-2).

**`Str`** (есть 19 методов):
`random()`, `mask()`, `studly()`, `limit()` (по символам, в отличие от `truncate()` по словам),
`contains()`, `startsWith()`/`endsWith()` (mb-варианты), `ucfirst()`/`lcfirst()` (mb),
`padBoth()`, `wrap()`, `between()`, `after()`/`before()`, `squish()`, `title()`,
`uuid()`/`ulid()`, `clearCache()` (P1-4).

**`Json`:** `decodeOrThrow()`, `encodeOrThrow()`, `isValid()`, `prettyPrint()` (P2-10).

**`Number`:** `format()`, `clamp()`, `percentage()`, `humanize()`, `ordinal()`.

**`Bit`:** `toggleFlag()`, `flags(int $mask): int[]`, `hasAll()`/`hasAny()`.

**`B64`:** RFC 4648-совместимый режим (P1-20).

### 10.2 API — коллекции

`sum()`, `avg()`, `min()`, `max()`, `median()`, `pluck()`, `unique()`, `keyBy()`, `flatten()`,
`implode()`, `tap()`, `pipe()`, `when()`/`unless()`, `toJson()`, `diff()`, `intersect()`,
`values()`, `flip()`, `zip()`, `take()`/`skip()`, `every()`/`some()`, `countBy()`,
`lazy()`/`Generator`-обёртка для больших наборов.

Для `HashCollection` — весь набор из P2-5.

### 10.3 API — прочее

* `Php\Support\Exceptions\ExceptionInterface` (P2-1) — **приоритет**.
* `WithEnhances`: `tryFromName()`, `fromName()`, `labels()`, `random()`, `hasValue()` для int-энумов (P2-8).
* `Storage`: `IteratorAggregate`, `Arrayable`, `all()`, `merge()`, `only()`, `except()` (P2-4).
* `UseErrorsBox`: `addError()`, `firstError()`, ключи/коды (P2-9).
* Отсутствуют как категории: работа с датой/временем, `Uuid`/`Ulid`, `Result`/`Option`,
  `Pipeline`, `Macroable`, `Comparable`, `Money`/`Decimal`, кэш-абстракция. Это не обязательные пробелы — но пакет
  позиционируется как «collection of useful functions», а покрывает только строки/массивы/биты.

### 10.4 Инфраструктура

| Что                                                              | Зачем                                                                                                  |
|------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| `ext-json`, `ext-ctype` в `require`                              | P2-20                                                                                                  |
| `pull_request` в триггерах CI                                    | P2-22                                                                                                  |
| Отдельные джобы PHPCS и PHPStan                                  | сейчас PHPCS в CI нет вовсе                                                                            |
| `failOnWarning`/`failOnDeprecation`/`failOnNotice`/`failOnRisky` | P2-18 — без этого фиксы не защищены от регрессий                                                       |
| PHPStan level ≥ 6, `excludePaths` сняты, `tests` включены        | P2-14                                                                                                  |
| `phpstan-baseline.neon`                                          | переход на высокий level без остановки разработки                                                      |
| `.editorconfig`                                                  | нет; отступы держатся только на PHPCS                                                                  |
| `CONTRIBUTING.md`, `SECURITY.md`, шаблоны issue/PR               | нет                                                                                                    |
| `dependabot.yml` / Renovate                                      | 4 прямые зависимости устарели                                                                          |
| Загрузка покрытия в codecov                                      | бейдж есть, загрузки нет                                                                               |
| `composer normalize` в скриптах                                  | плагин подключён, скрипта нет                                                                          |
| `authors[].email`, `support.issues`, `homepage` в composer.json  | нет                                                                                                    |
| Матрица CI с `ext-mbstring` off                                  | `URLify::seemsUTF8()` имеет ветку без mbstring (`@codeCoverageIgnore`), которая никогда не проверяется |

---

## 11. Что уже не нужно (легаси и мусор)

| #    | Кандидат                                                    | Почему                                                                                                                                                                                                                                                                                                                                                                                                                                              |
|------|-------------------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| L-1  | `candidates/Traits/HasMutator.php`                          | В git с 2019 года, **не в автозагрузке** (`composer.json` не содержит `candidates/`), ссылается на несуществующий класс `Php\Support\Candidates\Helpers\Str`. Мёртвый код, который невозможно использовать                                                                                                                                                                                                                                          |
| L-2  | `infection.json` + скрипт `composer infection`              | `infection/infection` не установлен → скрипт падает. В конфиге плейсхолдер из шаблона (`"NameSpace\\*\\Class::method"`), `tmpDir: /tmp`. Либо добавить зависимость и настроить, либо удалить и то и другое                                                                                                                                                                                                                                          |
| L-3  | Бейджи Codacy / CodeClimate / codecov в README              | Codacy на legacy `api.codacy.com`; CodeClimate Quality свёрнут; codecov указывает на несуществующую ветку `v2`. Три бейджа вводят в заблуждение                                                                                                                                                                                                                                                                                                     |
| L-4  | `remoteStaticCallOrTrow()` (`base.php:274`)                 | Алиас с опечаткой, уже `@deprecated`. Удалить в 6.0                                                                                                                                                                                                                                                                                                                                                                                                 |
| L-5  | `Interfaces/Prototype`                                      | Объявляет единственный метод `__clone()`, который есть у любого объекта PHP. Не реализован ни одним классом пакета, не несёт семантики                                                                                                                                                                                                                                                                                                              |
| L-6  | `Interfaces/Command`                                        | `execute(): void` — не используется ни в пакете, ни в тестах; дублирует общеизвестные контракты (`__invoke`, PSR-подобные Command). Без реализаций и документации бесполезен                                                                                                                                                                                                                                                                        |
| L-7  | `src/Testing/*` (2 трейта)                                  | Лежит в `src/`, но `AdditionalAssertionsTrait` требует `PHPUnit\Framework\TestCase` из `require-dev` — прод-код зависит от dev-зависимости. Покрытие 0%. `assertClassUsesTraits()` **переизобретает** уже имеющиеся в пакете `trait_uses_recursive()`/`class_uses_recursive()` инлайн-замыканиями. `TestingHelper` использует deprecated `setAccessible()`. Кандидат: вынести в `efureev/support-testing` или перевести на `suggest`/`autoload-dev` |
| L-8  | `Helpers/URLify.php` (794 строки, покрытие 9.3%)            | Вендоренная копия [jbroadway/urlify](https://github.com/jbroadway/urlify) без указания в `composer.json` — обновления безопасности и новые языковые карты не приходят. Заменяется `symfony/string` (`AsciiSlugger`) или `ext-intl` `Transliterator`. `public static array $maps` — публичное изменяемое глобальное состояние                                                                                                                        |
| L-9  | `Str::seemsUTF8()` (`Str.php:356`)                          | Однострочное проксирование в `URLify::seemsUTF8()`. Дубль публичного API                                                                                                                                                                                                                                                                                                                                                                            |
| L-10 | `Bit::exist()` (`Bit.php:79`)                               | Тривиальная обёртка `checkFlag(grant($list), $bit)` с грамматически неверным именем                                                                                                                                                                                                                                                                                                                                                                 |
| L-11 | `URLify::seemsUTF8Regex()` (ветка без mbstring)             | Помечена `@codeCoverageIgnore`, недостижима — `ext-mbstring` в `require` обязателен. Либо убрать требование расширения, либо удалить ветку                                                                                                                                                                                                                                                                                                          |
| L-12 | `.phpcs.xml`: `<exclude-pattern>tests/bootstrap.php`        | Файла не существует; комментарий «The testing bootstrap file uses string concats…» описывает несуществующую ситуацию                                                                                                                                                                                                                                                                                                                                |
| L-13 | `.github/workflows/php.yml:5-12`                            | Закомментированный джоб PHPCS. Либо включить, либо удалить                                                                                                                                                                                                                                                                                                                                                                                          |
| L-14 | `changelog.js:16` — regex `20[12][0-9]`                     | Даты с 2030 года не пройдут линт                                                                                                                                                                                                                                                                                                                                                                                                                    |
| L-15 | Аннотации `@psalm-*` в `ArrayCollection.php`                | Psalm не в зависимостях и не в CI — аннотации не читаются. Перевести на `@phpstan-*`                                                                                                                                                                                                                                                                                                                                                                |
| L-16 | Локальные артефакты в рабочей копии                         | `.DS_Store`, `.git-hooks/`, `clover.xml` (106 КБ), `storage/coverage/`, `src/.phpunit.result.cache`, `tests/.phpunit.result.cache`, `tests/Helpers/.phpunit.result.cache`, `tests/Traits/.phpunit.result.cache`. Все в `.gitignore`, но захламляют дерево; `.phpunit.result.cache` в `src/` — следствие относительного `cacheDirectory`                                                                                                             |
| L-17 | `symfony/var-dumper` в `require-dev`                        | Не используется (P2-23)                                                                                                                                                                                                                                                                                                                                                                                                                             |
| L-18 | `MissingConfigException::$needKey`                          | Свойство объявлено, нигде не читается, в сообщение не попадает                                                                                                                                                                                                                                                                                                                                                                                      |
| L-19 | `MethodNotAllowedException`: `$this->reason = $reason;`     | Дублирует promoted-свойство                                                                                                                                                                                                                                                                                                                                                                                                                         |
| L-20 | Property hook в `MissingMethodException`                    | `get { return $this->method; }` эквивалентен обычному свойству — синтаксический шум                                                                                                                                                                                                                                                                                                                                                                 |
| L-21 | Дублирование `ConfigurableTrait` ↔ `UseConfigurableStorage` | После фикса P0-3 нужно решить, нужны ли оба трейта, или `ConfigurableTrait` полностью поглощается вторым                                                                                                                                                                                                                                                                                                                                            |

---

## 12. План по релизам

Оценка трудоёмкости: **S** ≤ 1 ч, **M** — 1–4 ч, **L** — больше дня.

### 5.2.1 — 5.2.3 · Патчи, обратная совместимость сохранена

| Задача                                                                                                                                                                | ID                        | Оценка |
|-----------------------------------------------------------------------------------------------------------------------------------------------------------------------|---------------------------|--------|
| Переписать `Arr::toPostgresArray()` с корректным экранированием + раундтрип-тесты                                                                                     | P0-1                      | **M**  |
| `ArrayCollection`/`HashCollection` → `JsonSerializable` + `Arrayable`                                                                                                 | P0-2                      | **S**  |
| Починить цепочку `applyValue` в `UseConfigurableStorage`, переписать тесты                                                                                            | P0-3                      | **M**  |
| `propertyExists()` через `ReflectionProperty::isInitialized()`; `__unset()` → `unset()`                                                                               | P0-4                      | **M**  |
| Валидация формата в `Arr::fromPostgresPoint()`                                                                                                                        | P0-5                      | **S**  |
| Валидация `Str::truncate($length)`, `Bit::toInt()`, `slugifyWithFormat($format)`                                                                                      | P1-2, P1-3, P1-6          | **M**  |
| `Arr::merge()` → `array_key_exists`; `fillKeysByValues()` → `array_values`                                                                                            | P1-10, P1-11              | **S**  |
| Парсер `fromPostgresArrayWithBraces()`: не терять `''`                                                                                                                | P1-12                     | **M**  |
| `Point::fromJson()`/`GeoPoint::fromJson()` — проверка ключей; `GeoPoint::toJson(): ?string`; убрать `@throws JsonException`                                           | P1-13, P1-14              | **S**  |
| `getProperty($throwOnMiss)` — бросать для массивов                                                                                                                    | P1-15                     | **S**  |
| `getCallbackActions()` → `$key !== null`                                                                                                                              | P1-16                     | **S**  |
| `ConsolePrint` → `php://stdout` / `php://stderr`                                                                                                                      | P1-17                     | **S**  |
| Убрать `setAccessible()` из `src/Testing/` и `tests/`                                                                                                                 | P1-19                     | **S**  |
| `chunk(0)` → исключение; `random()` → `createFrom()`                                                                                                                  | P2-6                      | **S**  |
| `Arr::remove($separator)`; `Arr::set()` возвращает весь массив                                                                                                        | P2-2, P2-3                | **S**  |
| `declare(strict_types=1)` в `base.php` и `ConfigurableTrait.php`                                                                                                      | P2-17                     | **S**  |
| `ext-json`, `ext-ctype` в `require`                                                                                                                                   | P2-20                     | **S**  |
| `failOnWarning` + `failOnDeprecation` в `phpunit.xml`; схема → 12.5                                                                                                   | P2-18, P2-19              | **S**  |
| `on: [push, pull_request]`; включить джоб PHPCS; убрать лишний `XDEBUG_MODE`                                                                                          | P2-22, L-13               | **S**  |
| Тег-шаблон в `release.yml` → `v[0-9]+.[0-9]+.[0-9]+`                                                                                                                  | P2-21                     | **S**  |
| Удалить `candidates/`, `infection.json` + скрипт, `symfony/var-dumper`, мёртвые бейджи, `exclude-pattern tests/bootstrap.php`                                         | L-1, L-2, L-3, L-12, L-17 | **S**  |
| Почистить мёртвые куски в исключениях: `MissingConfigException::$needKey`, дубль присваивания в `MethodNotAllowedException`, property hook в `MissingMethodException` | L-18, L-19, L-20          | **S**  |
| Убрать локальные артефакты из дерева; починить относительный `cacheDirectory` в `phpunit.xml`                                                                         | L-16                      | **S**  |
| Обновить README: версия установки, `Testing/*`, `URLify`, `Storage`, `HashCollection`                                                                                 | P2-25                     | **M**  |
| Исправить 4 warning'а PHPCS (длина строк)                                                                                                                             | P2-24                     | **S**  |
| Обновить прямые зависимости; добавить dependabot                                                                                                                      | P2-23                     | **S**  |

**Итого:** ~5 находок P0, 12 находок P1, вся инфраструктура. Оценка блока — 3–4 дня.

---

### 5.3.0 · Minor — только добавления

| Задача                                                                                                                            | ID              | Оценка |
|-----------------------------------------------------------------------------------------------------------------------------------|-----------------|--------|
| `Exceptions\ExceptionInterface` + реализация во всех 15 классах                                                                   | P2-1            | **M**  |
| `Storage`: `IteratorAggregate`, `Arrayable`, `all()`, опция разделителя                                                           | P2-4            | **M**  |
| `HashCollection`: `IteratorAggregate`, `map`/`filter`/`each`/`keys`/`values`                                                      | P2-5            | **M**  |
| `Json::decodeOrThrow()` / `encodeOrThrow()`                                                                                       | P2-10           | **S**  |
| `Arr::removeByValue($strict = false)`                                                                                             | P1-9            | **S**  |
| `UseErrorsBox::addError()` + `firstError()`; `setError()` → `@deprecated`                                                         | P2-9            | **S**  |
| `Thrower::throw(): never`, `Maker::make(): static`, `Singleton::getInstance(): static`                                            | P2-13           | **S**  |
| `hasValue()` для int-энумов; `tryFromName()`/`fromName()`/`labels()`                                                              | P2-8 (частично) | **M**  |
| `Str::clearCache()` + лимит кэша                                                                                                  | P1-4            | **M**  |
| Недостающие методы `Arr` (`only`, `except`, `pluck`, `first`, `last`, `flatten`, `dot`, `undot`, `keyBy`, `isAssoc`, `isList`)    | §10.1           | **L**  |
| Недостающие методы `Str` (`random`, `mask`, `studly`, `limit`, `contains`, `startsWith`/`endsWith`, `squish`)                     | §10.1           | **L**  |
| Недостающие методы коллекций (`sum`, `avg`, `min`, `max`, `pluck`, `unique`, `keyBy`, `implode`, `tap`, `pipe`, `when`, `toJson`) | §10.2           | **L**  |
| Снять `excludePaths` в `phpstan.neon`, поднять level до 6, добавить baseline                                                      | P2-14           | **M**  |
| Перевести `@psalm-*` → `@phpstan-*`                                                                                               | P2-16, L-15     | **S**  |
| Покрыть тестами: `Testing/*`, `URLify`, `Whener`, 6 классов исключений, `base.php`                                                | §7              | **L**  |
| `.editorconfig`, `CONTRIBUTING.md`, `SECURITY.md`, шаблоны issue/PR                                                               | §10.4           | **S**  |
| Секция `[Unreleased]` и ссылки сравнения в CHANGELOG; починить regex дат                                                          | P2-26, L-14     | **S**  |

**Оценка блока:** 1.5–2 недели.

---

### 6.0.0 · Major — ломающие изменения

| Задача                                                                                       | ID        | Оценка |
|----------------------------------------------------------------------------------------------|-----------|--------|
| Удалить `remoteStaticCallOrTrow()`                                                           | L-4       | **S**  |
| Удалить `Interfaces/Prototype`, `Interfaces/Command`                                         | L-5, L-6  | **S**  |
| Вынести `src/Testing/*` в отдельный пакет или в `autoload-dev`                               | L-7       | **M**  |
| Заменить вендоренный `URLify` на `symfony/string` / `ext-intl`                               | L-8, L-11 | **L**  |
| Единая сигнатура `casesToString()` в `WithEnhances*`; `@mixin \BackedEnum`; `hasName($name)` | P2-8      | **M**  |
| `ReadOnlyProperties` — только публичные свойства (или allow-list)                            | P2-7      | **M**  |
| `UseErrorsBox::setError()` удалить (остаётся `addError()`)                                   | P2-9      | **S**  |
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
| PHPStan level 8+ без baseline                                                                | P2-14     | **L**  |
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
