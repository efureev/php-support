<?php

declare(strict_types=1);

namespace Php\Support\Tests\Traits;

use Php\Support\Traits\UseErrorsBox;
use PHPUnit\Framework\TestCase;

final class UseErrorsBoxTest extends TestCase
{
    private function makeSubject(): object
    {
        return new class {
            use UseErrorsBox;
        };
    }

    public function testStartsWithoutErrors(): void
    {
        $subject = $this->makeSubject();

        self::assertFalse($subject->hasErrors());
        self::assertSame([], $subject->errors());
    }

    public function testSetErrorWithStringIsFluent(): void
    {
        $subject = $this->makeSubject();

        self::assertSame($subject, $subject->setError('oops'));
        self::assertTrue($subject->hasErrors());
        self::assertSame(['oops'], $subject->errors());
    }

    public function testSetErrorAccumulates(): void
    {
        $subject = $this->makeSubject();

        $subject->setError('first')->setError('second');

        self::assertSame(['first', 'second'], $subject->errors());
    }

    public function testSetErrorWithExceptionUsesMessage(): void
    {
        $subject = $this->makeSubject();

        $subject->setError(new \RuntimeException('exception message'));

        self::assertSame(['exception message'], $subject->errors());
    }

    public function testSetErrorWithErrorUsesMessageNotStackTrace(): void
    {
        $subject = $this->makeSubject();

        $subject->setError(new \Error('fatal message'));

        self::assertSame(['fatal message'], $subject->errors());
    }

    public function testClearErrors(): void
    {
        $subject = $this->makeSubject();

        $subject->setError('oops');

        self::assertSame($subject, $subject->clearErrors());
        self::assertFalse($subject->hasErrors());
        self::assertSame([], $subject->errors());
    }

    public function testAddErrorAppends(): void
    {
        $subject = $this->makeSubject();

        $subject->addError('first')->addError('second');

        self::assertSame(['first', 'second'], $subject->errors());
        self::assertSame(2, $subject->errorsCount());
    }

    public function testAddErrorWithKeyOverwrites(): void
    {
        $subject = $this->makeSubject();

        $subject->addError('one', 'field')->addError('two', 'field');

        self::assertSame(['field' => 'two'], $subject->errors());
        self::assertSame('two', $subject->error('field'));
        self::assertNull($subject->error('missing'));
    }

    public function testFirstError(): void
    {
        $subject = $this->makeSubject();

        self::assertNull($subject->firstError());

        $subject->addError('first')->addError('second');

        self::assertSame('first', $subject->firstError());
    }

    public function testSetErrorStillAppends(): void
    {
        $subject = $this->makeSubject();

        $subject->setError('one')->setError('two');

        self::assertSame(['one', 'two'], $subject->errors());
    }

    public function testAddErrorAcceptsThrowable(): void
    {
        $subject = $this->makeSubject();

        $subject->addError(new \RuntimeException('boom'));

        self::assertSame(['boom'], $subject->errors());
    }
}
