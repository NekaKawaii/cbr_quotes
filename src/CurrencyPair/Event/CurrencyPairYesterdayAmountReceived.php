<?php

declare(strict_types=1);

namespace App\CurrencyPair\Event;

use App\Type\Date;

/**
 * @psalm-immutable
 */
final class CurrencyPairYesterdayAmountReceived implements CurrencyPairSourceEvent
{
    /**
     * @param string $base Base currency for pair
     * @param string $quote Quote currency for pair
     * @param float $amount How much of the quote currency needed to purchase one unit of the base currency
     * @param Date $date Actual date for currency pair
     * @param float $yesterdayAmount Yesterday amount can be null if new data is far in the future
     * @param \DateTimeImmutable $occurredAt
     */
    public function __construct(
        public readonly string $base,
        public readonly string $quote,
        public readonly float $amount,
        public readonly Date $date,
        public readonly float $yesterdayAmount,
        public \DateTimeImmutable $occurredAt
    ) {
    }
}
