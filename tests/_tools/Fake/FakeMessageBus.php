<?php

declare(strict_types=1);

namespace App\Tests\_tools\Fake;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Fake message bus for test purposes
 */
final class FakeMessageBus implements MessageBusInterface
{
    /**
     * Dispatched messages
     *
     * @var array<object>
     */
    private array $dispatched = [];

    /**
     * @inheritDoc
     */
    public function dispatch(object $message, array $stamps = []): Envelope
    {
        $this->dispatched[] = $message;

        return Envelope::wrap($message, $stamps);
    }

    /**
     * @template T
     *
     * @psalm-param class-string<T> $messageClass
     *
     * @return array<T>
     */
    public function findDispatched(string $messageClass): array
    {
        return array_values(
            array_filter($this->dispatched, fn (object $message) => \is_a($message, $messageClass, true))
        );
    }

    /**
     * @psalm-param class-string $messageClass
     */
    public function isNotDispatched(string $messageClass): bool
    {
        return count($this->findDispatched($messageClass)) === 0;
    }
}
