<?php

declare(strict_types=1);

namespace App\CurrencyPairsParser\Event;

use App\Type\Date;

/**
 * Emitted by parser when it parses currency pair
 *
 * @psalm-immutable
 */
final class CurrencyPairReceived
{
    /**
     * @param string $base Base currency for pair
     * @param string $quote Quote currency for pair
     * @param float $amount How much of the quote currency needed to purchase one unit of the base currency
     * @param Date $date Actual date for currency pair
     * @param \DateTimeImmutable $occurredAt
     */
    public function __construct(
        public readonly string $base,
        public readonly string $quote,
        public readonly float $amount,
        public readonly Date $date,
        public \DateTimeImmutable $occurredAt
    ) {
    }
}
