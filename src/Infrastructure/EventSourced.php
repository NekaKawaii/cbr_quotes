<?php

declare(strict_types=1);

namespace App\Infrastructure;

use function App\getClassShortName;

/**
 * Entity that can emit events and can be recreated from stream of events
 */
abstract class EventSourced
{
    /**
     * Stream of emitted entity events
     *
     * @var array<object>
     */
    private array $events = [];

    /**
     * Emit event by entity
     */
    public function emit(object $event): void
    {
        $this->events[] = $event;

        $this->consume($event);
    }

    /**
     * Releasing all events emitted by entity
     *
     * @return array<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    /**
     * @param array<object> $events
     */
    public function applyEventStream(array $events): void
    {
        foreach ($events as $event) {
            $this->consume($event);
        }
    }

    /**
     * Consume emitted event by entity itself
     */
    private function consume(object $event): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $methodName = \sprintf('on%s', getClassShortName($event));

        if (\method_exists($this, $methodName) !== true) {
            return;
        }

        /** @psalm-suppress MissingClosureReturnType */
        (fn (object $event) => $this->{$methodName}($event))
            ->call($this, $event);
    }
}
