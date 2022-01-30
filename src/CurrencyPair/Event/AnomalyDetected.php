<?php

declare(strict_types=1);

namespace App\CurrencyPair\Event;

/**
 * @psalm-immutable
 */
final class AnomalyDetected
{
    /**
     * @param string $base Base currency for pair
     * @param string $quote Quote currency for pair
     * @param string $description Description of the anomaly
     * @param array<string, mixed> $extra Extra fields for research purposes
     * @param \DateTimeImmutable $occurredAt
     */
    public function __construct(
        public readonly string $base,
        public readonly string $quote,
        public readonly string $description,
        public readonly array $extra,
        public \DateTimeImmutable $occurredAt
    ) {
    }
}
