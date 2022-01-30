<?php

declare(strict_types=1);

namespace App\Tests\_tools;

use App\Infrastructure\EventSourced;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\IsEqual;
use function App\getPropertyValueByReflection;
use function App\now;
use function App\setPropertyValueByReflection;

/**
 * Assert that event sourced entity has no event of class in its stream
 */
final class HasNoEventInStream extends Constraint
{
    public function __construct(private EventSourced $eventSourced)
    {
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function matches($other): bool
    {
        /** @var class-string $other */

        /**
         * @var object $event
         * @psalm-suppress PossiblyNullIterator
         */
        foreach (getPropertyValueByReflection($this->eventSourced, 'events', EventSourced::class) as $event) {
            if (\get_class($event) === $other) {
                return false;
            }
        }

        return true;
    }

    public function toString(): string
    {
        return 'is not in event stream of object';
    }
}
