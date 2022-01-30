<?php

declare(strict_types=1);

namespace App\ReadModel\CurrencyPair;

use App\CurrencyPair\Event\CurrencyPairCreated;
use App\CurrencyPair\Event\CurrencyPairCurrentAmountUpdated;
use App\CurrencyPair\Event\CurrencyPairUpdated;
use App\CurrencyPair\Event\CurrencyPairYesterdayAmountReceived;
use function App\getDeltaPercentageWithBCMath;

/**
 * @psalm-readonly
 */
final class CurrencyPairDynamicsProjection
{
    public function __construct(
        public string $base,
        public string $quote,
        public string $date,
        public float $current,
        public ?float $previous,
        public ?string $delta,
        public string $lastUpdatedAt
    ) {
    }

    public function onCurrencyPairCreated(CurrencyPairCreated $event): void
    {
        $this->base = $event->base;
        $this->quote = $event->quote;
        $this->date = (string)$event->date;
        $this->current = $event->amount;
        $this->lastUpdatedAt = $event->occurredAt->format(DATE_ATOM);

        // Reset all other fields because currency pair just created
        $this->previous = null;
        $this->delta = null;
    }

    public function onCurrencyPairUpdated(CurrencyPairUpdated $event): void
    {
        $this->previous = $this->current;
        $this->current = $event->amount;
        $this->date = (string)$event->date;
        $this->lastUpdatedAt = $event->occurredAt->format(DATE_ATOM);
        $this->recalculateDelta();
    }

    public function onCurrencyPairYesterdayAmountReceived(CurrencyPairYesterdayAmountReceived $event): void
    {
        $this->previous = $event->yesterdayAmount;
        $this->lastUpdatedAt = $event->occurredAt->format(DATE_ATOM);
        $this->recalculateDelta();
    }

    public function onCurrencyPairCurrentAmountUpdated(CurrencyPairCurrentAmountUpdated $event): void
    {
        $this->current = $event->amount;
        $this->lastUpdatedAt = $event->occurredAt->format(DATE_ATOM);
        $this->recalculateDelta();
    }

    private function recalculateDelta(): void
    {
        if ($this->previous === null) {
            return;
        }

        $this->delta = getDeltaPercentageWithBCMath($this->previous, $this->current) . '%';
    }
}
