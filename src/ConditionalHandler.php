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
    private array $params = [];

    private bool|Closure $condition = true;

    public function __construct(private Closure $handler)
    {
    }

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
     *
     * @return mixed
     */
    public function __invoke(mixed ...$params)
    {
        return $this->resolve(...$params);
    }

    public static function make(Closure $fn): self
    {
        return new self($fn);
    }
}
