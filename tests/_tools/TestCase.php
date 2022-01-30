<?php

declare(strict_types=1);

namespace App\Tests\_tools;

use App\Infrastructure\EventSourced;

class TestCase extends \PHPUnit\Framework\TestCase
{
    public static function assertObjectHasEventInStream(EventSourced $eventSourced, object $event, string $message = ''): void
    {
        self::assertThat($event, new HasEventInStream($eventSourced), $message);
    }

    /**
     * @psalm-param class-string $eventClass
     */
    public static function assertObjectHasNoEventInStream(EventSourced $eventSourced, string $eventClass, string $message = ''): void
    {
        self::assertThat($eventClass, new HasNoEventInStream($eventSourced), $message);
    }
}
