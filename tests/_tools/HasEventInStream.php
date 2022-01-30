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
 * Assert that event sourced entity has event in its stream
 */
final class HasEventInStream extends Constraint
{
    public function __construct(private EventSourced $eventSourced)
    {
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function matches($other): bool
    {
        /** @var object $other */

        /**
         * We don't care about actual time in the occurredAt property
         *
         * @psalm-suppress MixedPropertyAssignment
        */
        $other->occurredAt = $now = now();

        /**
         * @var object $event
         * @psalm-suppress PossiblyNullIterator
         */
        foreach (getPropertyValueByReflection($this->eventSourced, 'events', EventSourced::class) as $event) {
            $event->occurredAt = $now;

            if ((new IsEqual($event))->evaluate($other, returnResult: true)) {
                return true;
            }
        }

        return false;
    }

    public function toString(): string
    {
        return 'is in event stream of object';
    }
}
