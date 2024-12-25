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
final class ConditionalHandler
{
    /**
     * @var array
     * @phpstan-ignore missingType.iterableValue
     */
    private array $params = [];

    /**
     * @param Closure(mixed ...): mixed $handler
     * @param bool|(Closure(mixed ...): bool) $condition
     */
    public function __construct(private Closure $handler, private bool|Closure $condition = true)
    {
    }

    /**
     * @param (Closure(mixed ...): bool)|bool $fn
     * @return ConditionalHandler
     */
    public function handleIf(Closure|bool $fn): self
    {
        $this->condition = $fn;

        return $this;
    }

    private function resolveCondition(): bool
    {
        if ($this->condition instanceof Closure) {
            return ($this->condition)(...$this->params);
        }

        return $this->condition;
    }

    /**
     * @param mixed ...$params
     */
    public function resolve(mixed ...$params): mixed
    {
        $this->params = $params;

        if (!$this->resolveCondition()) {
            return null;
        }

        return ($this->handler)(...$this->params);
    }

    /**
     * @param mixed ...$params
     */
    public function __invoke(mixed ...$params): mixed
    {
        return $this->resolve(...$params);
    }

    /**
     * @param Closure(mixed ...): mixed $fn
     * @param bool|(Closure(mixed ...): bool) $condition
     * @return ConditionalHandler
     */
    public static function make(Closure $fn, bool|Closure $condition = true): self
    {
        return new self($fn, $condition);
    }
}
