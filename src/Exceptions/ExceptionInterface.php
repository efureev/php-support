<?php

declare(strict_types=1);

namespace Php\Support\Exceptions;

use Throwable;

/**
 * Marker implemented by every exception this package throws.
 *
 * The concrete classes extend different SPL exceptions - `LogicException`,
 * `BadMethodCallException`, `UnexpectedValueException` and so on - so without this
 * marker there is no way to catch "anything from this package" in a single block:
 *
 * ```php
 * try {
 *     // ...
 * } catch (\Php\Support\Exceptions\ExceptionInterface $e) {
 *     // every exception of the package, whatever it extends
 * }
 * ```
 */
interface ExceptionInterface extends Throwable
{
}
