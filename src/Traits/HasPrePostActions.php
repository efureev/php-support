<?php

declare(strict_types=1);

namespace Php\Support\Traits;

trait HasPrePostActions
{
    /** @var array<string,array<callable>> */
    protected array $executeCallbacks = [];

    public function addCallbackAction(string $key, callable $action): self
    {
        $this->executeCallbacks[$key][] = $action;

        return $this;
    }

    public function getCallbackActions(?string $key = null): array
    {
        return $key ?
            $this->executeCallbacks[$key] ?? []
            : $this->executeCallbacks;
    }

    protected function runActions(string $actionGroup, ...$arguments): bool
    {
        foreach ($this->getCallbackActions($actionGroup) as $action) {
            $res = $action(...$arguments);
            if ($res === false) {
                return false;
            }
        }

        return true;
    }
}
