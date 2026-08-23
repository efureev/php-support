<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\HasPrePostActions;
use PHPUnit\Framework\TestCase;

final class HasPrePostActionsTest extends TestCase
{
    private function makeSubject(): object
    {
        return new class {
            use HasPrePostActions;

            public function run(string $group, mixed ...$arguments): bool
            {
                return $this->runActions($group, ...$arguments);
            }
        };
    }

    public function testAddCallbackActionIsFluent(): void
    {
        $subject = $this->makeSubject();

        self::assertSame($subject, $subject->addCallbackAction('pre', static fn () => true));
    }

    public function testGetCallbackActionsByKey(): void
    {
        $subject = $this->makeSubject();
        $action  = static fn () => true;

        $subject->addCallbackAction('pre', $action);

        self::assertSame([$action], $subject->getCallbackActions('pre'));
        self::assertSame([], $subject->getCallbackActions('missing'));
    }

    public function testGetAllCallbackActions(): void
    {
        $subject = $this->makeSubject();
        $first   = static fn () => true;
        $second  = static fn () => true;

        $subject->addCallbackAction('pre', $first);
        $subject->addCallbackAction('post', $second);

        self::assertSame(
            [
                'pre'  => [$first],
                'post' => [$second],
            ],
            $subject->getCallbackActions()
        );
    }

    public function testRunActionsExecutesAllAndReturnsTrue(): void
    {
        $subject = $this->makeSubject();
        $calls   = [];

        $subject->addCallbackAction(
            'pre',
            function () use (&$calls): void {
                $calls[] = 'a';
            }
        );
        $subject->addCallbackAction(
            'pre',
            function () use (&$calls): void {
                $calls[] = 'b';
            }
        );

        self::assertTrue($subject->run('pre'));
        self::assertSame(['a', 'b'], $calls);
    }

    public function testRunActionsPassesArguments(): void
    {
        $subject  = $this->makeSubject();
        $received = null;

        $subject->addCallbackAction(
            'pre',
            function (int $x, int $y) use (&$received): void {
                $received = $x + $y;
            }
        );

        self::assertTrue($subject->run('pre', 2, 3));
        self::assertSame(5, $received);
    }

    public function testRunActionsStopsOnFalse(): void
    {
        $subject = $this->makeSubject();
        $calls   = [];

        $subject->addCallbackAction(
            'pre',
            function () use (&$calls): bool {
                $calls[] = 'a';

                return false;
            }
        );
        $subject->addCallbackAction(
            'pre',
            function () use (&$calls): void {
                $calls[] = 'b';
            }
        );

        self::assertFalse($subject->run('pre'));
        self::assertSame(['a'], $calls);
    }

    public function testRunActionsWithoutCallbacksReturnsTrue(): void
    {
        self::assertTrue($this->makeSubject()->run('empty'));
    }

    public function testZeroNamedGroupIsNotTreatedAsMissingKey(): void
    {
        $subject = $this->makeSubject();
        $action  = static fn () => true;

        $subject->addCallbackAction('0', $action);
        $subject->addCallbackAction('other', static fn () => true);

        self::assertSame([$action], $subject->getCallbackActions('0'));
    }
}
