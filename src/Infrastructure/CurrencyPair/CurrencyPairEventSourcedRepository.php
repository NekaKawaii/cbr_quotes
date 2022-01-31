<?php

declare(strict_types=1);

namespace App\Infrastructure\CurrencyPair;

use App\CurrencyPair\CurrencyPair;
use App\CurrencyPair\CurrencyPairRepository;
use App\CurrencyPair\Event\CurrencyPairSourceEvent;
use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use function App\createObjectWithoutConstructor;

/**
 * RDBMS evens store repository implementation
 */
final class CurrencyPairEventSourcedRepository implements CurrencyPairRepository
{
    public function __construct(
        private Connection $db,
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer
    ) {
    }

    public function find(string $base, string $quote): ?CurrencyPair
    {
        /** @var array<array{payload: string, event_class: class-string}> $eventRows */
        $eventRows = $this->db->fetchAllAssociative('
            SELECT event_class, payload FROM currency_pair_stream
            WHERE base = ? AND quote = ?
            ORDER BY occurred_at ASC
        ', [$base, $quote], [\PDO::PARAM_STR, \PDO::PARAM_STR]);

        if (count($eventRows) === 0) {
            return null;
        }


        /** @var array<object> $events */
        $events = array_map(
            /**
             * @param array{payload: string, event_class: class-string} $row
             * @return mixed
             */
            fn (array $row) => $this->serializer->deserialize($row['payload'], $row['event_class'], 'json'),
            $eventRows
        );

        $pair = createObjectWithoutConstructor(CurrencyPair::class);
        $pair->applyEventStream($events);

        return $pair;
    }

    /**
     * @psalm-suppress NoInterfaceProperties
     */
    public function save(CurrencyPair $pair): void
    {
        $allEvents = $pair->releaseEvents();

        foreach ($allEvents as $event) {
            $this->messageBus->dispatch($event);
        }

        $sourceEvents = array_filter($allEvents, fn (object $event) => $event instanceof CurrencyPairSourceEvent);

        foreach ($sourceEvents as $event) {
            $this->db->insert('currency_pair_stream', [
                'base' => $event->base,
                'quote' => $event->quote,
                'event_class' => \get_class($event),
                'payload' => $this->serializer->serialize($event, 'json'),
                'occurred_at' => $event->occurredAt
            ], [
                'occurred_at' => 'datetime_utc_nanoseconds'
            ]);
        }
    }
}
