<?php

declare(strict_types=1);

namespace Php\Support;

use Closure;

/**
 * Perform an action if condition is true
 *
 * @example
 *
 * // create
 * $field = ConditionalHandler::make(
 *     static fn(Request $request) => MorphMany::make(
 *         self::translate('Notifications'),
 *         'notifications',
 *         NotificationResource::class
 *     )
 * )
 * ->handleIf(static function (Request $request) {
 *     $request->user()->id === Auth()->id()
 * });
 *
 * // call
 * $field($request);
 *   // or
 * $field->resolve($request);
 */
final readonly class ConditionalHandler
{
    /**
     * The closures receive whatever is passed to resolve()/__invoke(), so their parameters are
     * deliberately unconstrained: `Closure(mixed ...)` would reject every concretely-typed
     * callback a caller actually writes.
     *
     * @param Closure $handler
     * @param bool|Closure $condition
     */
    public function __construct(private Closure $handler, private bool|Closure $condition = true)
    {
    }

    /**
     * @param Closure|bool $fn
     * @return ConditionalHandler
     */
    public function handleIf(Closure|bool $fn): self
    {
        return new self($this->handler, $fn);
    }

    /**
     * @param mixed ...$params
     */
    private function resolveCondition(mixed ...$params): bool
    {
        if ($this->condition instanceof Closure) {
            return ($this->condition)(...$params);
        }

        return $this->condition;
    }

    /**
     * @param mixed ...$params
     */
    public function resolve(mixed ...$params): mixed
    {
        if (!$this->resolveCondition(...$params)) {
            return null;
        }

        return ($this->handler)(...$params);
    }

    /**
     * @param mixed ...$params
     */
    public function __invoke(mixed ...$params): mixed
    {
        return $this->resolve(...$params);
    }

    /**
     * @param Closure $fn
     * @param bool|Closure $condition
     * @return ConditionalHandler
     */
    public static function make(Closure $fn, bool|Closure $condition = true): self
    {
        return new self($fn, $condition);
    }
}
