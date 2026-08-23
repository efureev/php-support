# Аудит пакета `efureev/support`

**Аудит проведён:** 2026-08-23 на ревизии `c0c745e` · **Версия пакета:** 5.2.0
**Требования пакета:** PHP `^8.4`, `ext-ctype`, `ext-json`, `ext-mbstring`

> **Статус.** Блок 5.2.x реализован в ветке `fix/5.2.x-audit` (см. `CHANGELOG.md`, секция `v5.2.1`).
> **Из этого файла удалено всё, что уже сделано** — здесь остались только открытые находки.
> История закрытых лежит в git: `git log --oneline master..fix/5.2.x-audit`.

---

## 1. Резюме

`efureev/support` — библиотека общего назначения: хелперы (`Arr`, `Str`, `Json`, `Bit`, `B64`,
`Number`, `URLify`), коллекции, трейты, исключения, типы. 53 файла в `src/`, 4074 NCLOC.

### Текущее состояние (замерено после блока 5.2.x)

| Метрика                      | Значение                       | Комментарий                                       |
|------------------------------|--------------------------------|---------------------------------------------------|
| Тесты                        | **507 зелёных**, 3074 ассерта  | 0 deprecation, 0 notice; `failOn*` включены       |
| Покрытие строк               | **83.96%** (895/1066)          | 7 файлов с 0%, ещё 3 ниже 50%                     |
| Покрытие методов             | **88.26%** (233/264)           |                                                   |
| PHPStan (как настроен)       | **0 ошибок**                   | **но level 2 и 3 файла в `excludePaths`**         |
| PHPStan level 9              | **65 ошибок**                  | без учёта исключённых файлов                      |
| PHPCS (PSR-12)               | 0 errors, 0 warnings           |                                                   |
| `composer validate --strict` | OK                             |                                                   |

### Главный вывод

Блок 5.2.x закрыл все критичные находки и сделал сборку честной: `failOnWarning`/`failOnDeprecation`/
`failOnNotice`/`failOnRisky` включены, и прогон тестов впервые даёт чистый `OK` вместо
«OK, but there were issues!».

Осталась главная системная проблема: **PHPStan по-прежнему на level 2 и не анализирует
`ArrayCollection` (876 строк) вместе с `Point`/`GeoPoint`** — они в `excludePaths`. На level 9 это
65 ошибок. Пока конфиг не поднят, статический анализ не защищает самый крупный класс пакета.

Открытых находок: **8 P1**, **15 P2**, **10 кандидатов на удаление**. Ни одна не приводит к потере
данных — это API-контракты, типизация и полнота покрытия. Разбивка по релизам — в разделе 10.

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
| `src/Helpers/Str.php:263`                            | `replaceByTemplate(string $str, …): array\|string` — вход `string`, `str_replace()` всегда вернёт `string`. Тип возврата излишне широк                              |
| `src/Helpers/Bit.php:79`                             | `exist(array $list, int $bit)` — тривиальная обёртка `checkFlag(grant($list), $bit)` со сломанным неймингом                                                         |
| `src/Helpers/Str.php:356`                            | `seemsUTF8()` — чистое проксирование в `URLify::seemsUTF8()`                                                                                                        |
| `src/Global/base.php:61`                             | `dataGet()` использует `isset($target->{$segment})` — публичное свойство со значением `null` даёт `$default` вместо `null`                                          |

---

## 5. Типизация и статический анализ

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
   проверяются `Point`/`GeoPoint`. Два дефекта типизации в них (`fromJson`, `toJson`) пришлось искать
   вручную именно потому, что PHPStan их не видит.
3. `tests` закомментированы — тесты не типизируются.
4. Замеры по уровням (без исключённых файлов): level 5 → 22, level 6 → 24, level 8 → 39, level 9 → **65 ошибок**.

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

## 6. Тесты и качество

### Покрытие по файлам

Замер после блока 5.2.x: **83.96%** строк (895/1066), **88.26%** методов (233/264).

|  Покрытие | Файл                                                                                                                                                                 | Комментарий                                      |
|----------:|----------------------------------------------------------------------------------------------------------------------------------------------------------------------|--------------------------------------------------|
|    **0%** | `src/Testing/AdditionalAssertionsTrait.php` (0/26)                                                                                                                   |                                                  |
|    **0%** | `src/Testing/TestingHelper.php` (0/7)                                                                                                                                |                                                  |
|    **0%** | `Exceptions/InvalidCallException`, `InvalidConfigException`, `InvalidValueException`, `MethodNotAllowedException`, `MissingMethodException`                          | 5 классов исключений без единого теста           |
|  **9.3%** | `src/Helpers/URLify.php` (5/54)                                                                                                                                      | 794 строки, покрыт только `downcode` по латинице |
|   **40%** | `src/Traits/Whener.php`                                                                                                                                              | единственный тест — `when(true, fn() => 1)`      |
| **47.9%** | `src/Global/base.php` (45/94)                                                                                                                                        | половина глобальных функций не тестируется       |
| **85.7%** | `src/Traits/Singleton.php`                                                                                                                                           |                                                  |
| **90.9%** | `src/Traits/UseStorage.php`                                                                                                                                          |                                                  |

### P2-19 · Прочее по тестам

* Нет тестов на: мультибайтовые входы `Str`, наследование коллекций (`createFrom` в наследнике),
  не-CLI-режим `ConsolePrint`, трейты `Testing/*`, `URLify`.
* `beStrictAboutOutputDuringTests` не включён: `ConsolePrintTest` печатает в отчёт. Включать можно
  только вместе с переводом этого теста на буферизацию.

---

## 7. Инфраструктура

### P2-23 · Обновление зависимостей

* `phpunit 12.5 → 13.3` — мажор, требует отдельной проверки на несовместимости.
* Констрейнты `^2.2` / `^4.0` намеренно не сужались: они и так допускают текущие патч-версии,
  а сужение ограничило бы потребителей.

---

## 8. Чего не хватает

### 8.1 API — хелперы

**`Arr`** (есть 23 метода, нет базового набора):
`only()`, `except()`, `pluck()`, `first()`, `last()`, `flatten()`, `wrap()`, `dot()`, `undot()`,
`keyBy()`, `where()`, `isAssoc()`, `isList()`, `sortRecursive()`, `divide()`, `crossJoin()`,
`shuffle()`, `whereNotNull()`. Плюс к существующим: `removeByValue(bool $strict)` (P1-9).

**`Str`** (есть 19 методов):
`random()`, `mask()`, `studly()`, `limit()` (по символам, в отличие от `truncate()` по словам),
`contains()`, `startsWith()`/`endsWith()` (mb-варианты), `ucfirst()`/`lcfirst()` (mb),
`padBoth()`, `wrap()`, `between()`, `after()`/`before()`, `squish()`, `title()`,
`uuid()`/`ulid()`, `clearCache()` (P1-4).

**`Json`:** `decodeOrThrow()`, `encodeOrThrow()`, `isValid()`, `prettyPrint()` (P2-10).

**`Number`:** `format()`, `clamp()`, `percentage()`, `humanize()`, `ordinal()`.

**`Bit`:** `toggleFlag()`, `flags(int $mask): int[]`, `hasAll()`/`hasAny()`.

**`B64`:** RFC 4648-совместимый режим (P1-20).

### 8.2 API — коллекции

`sum()`, `avg()`, `min()`, `max()`, `median()`, `pluck()`, `unique()`, `keyBy()`, `flatten()`,
`implode()`, `tap()`, `pipe()`, `when()`/`unless()`, `toJson()`, `diff()`, `intersect()`,
`values()`, `flip()`, `zip()`, `take()`/`skip()`, `every()`/`some()`, `countBy()`,
`lazy()`/`Generator`-обёртка для больших наборов.

Для `HashCollection` — весь набор из P2-5.

### 8.3 API — прочее

* `Php\Support\Exceptions\ExceptionInterface` (P2-1) — **приоритет**.
* `WithEnhances`: `tryFromName()`, `fromName()`, `labels()`, `random()`, `hasValue()` для int-энумов (P2-8).
* `Storage`: `IteratorAggregate`, `Arrayable`, `all()`, `merge()`, `only()`, `except()` (P2-4).
* `UseErrorsBox`: `addError()`, `firstError()`, ключи/коды (P2-9).
* Отсутствуют как категории: работа с датой/временем, `Uuid`/`Ulid`, `Result`/`Option`,
  `Pipeline`, `Macroable`, `Comparable`, `Money`/`Decimal`, кэш-абстракция. Это не обязательные пробелы — но пакет
  позиционируется как «collection of useful functions», а покрывает только строки/массивы/биты.

### 8.4 Инфраструктура

| Что                                                              | Зачем                                                                                                  |
|------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| PHPStan level ≥ 6, `excludePaths` сняты, `tests` включены        | P2-14                                                                                                  |
| `phpstan-baseline.neon`                                          | переход на высокий level без остановки разработки                                                      |
| `.editorconfig`                                                  | нет; отступы держатся только на PHPCS                                                                  |
| `CONTRIBUTING.md`, `SECURITY.md`, шаблоны issue/PR               | нет                                                                                                    |
| Загрузка покрытия в codecov или Coveralls                        | сейчас покрытие в CI не собирается вовсе                                                               |
| `composer normalize` в скриптах                                  | плагин подключён, скрипта нет                                                                          |
| `authors[].email`, `support.issues`, `homepage` в composer.json  | нет                                                                                                    |
| Матрица CI с `ext-mbstring` off                                  | `URLify::seemsUTF8()` имеет ветку без mbstring (`@codeCoverageIgnore`), которая никогда не проверяется |

---

## 9. Что уже не нужно (легаси и мусор)

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

## 10. План по релизам

Оценка трудоёмкости: **S** ≤ 1 ч, **M** — 1–4 ч, **L** — больше дня.

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
| Недостающие методы `Arr` (`only`, `except`, `pluck`, `first`, `last`, `flatten`, `dot`, `undot`, `keyBy`, `isAssoc`, `isList`)    | §8.1           | **L**  |
| Недостающие методы `Str` (`random`, `mask`, `studly`, `limit`, `contains`, `startsWith`/`endsWith`, `squish`)                     | §8.1           | **L**  |
| Недостающие методы коллекций (`sum`, `avg`, `min`, `max`, `pluck`, `unique`, `keyBy`, `implode`, `tap`, `pipe`, `when`, `toJson`) | §8.2           | **L**  |
| Снять `excludePaths` в `phpstan.neon`, поднять level до 6, добавить baseline                                                      | P2-14           | **M**  |
| Перевести `@psalm-*` → `@phpstan-*`                                                                                               | P2-16, L-15     | **S**  |
| Покрыть тестами: `Testing/*`, `URLify`, `Whener`, 5 классов исключений, `base.php`                                                | §6, P2-19       | **L**  |
| Обновить PHPUnit до 13.x                                                                                                          | P2-23           | **M**  |
| `.editorconfig`, `CONTRIBUTING.md`, `SECURITY.md`, шаблоны issue/PR                                                               | §8.4           | **S**  |
| Секция `[Unreleased]` и ссылки сравнения версий в CHANGELOG                                                                       | —               | **S**  |

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
