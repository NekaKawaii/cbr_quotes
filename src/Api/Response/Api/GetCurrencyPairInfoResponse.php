<?php

declare(strict_types=1);

namespace App\Api\Response\Api;

use App\Api\Response\Response;
use App\ReadModel\CurrencyPair\CurrencyPairDynamicsProjection;

/**
 * Currency pair info for api
 */
final class GetCurrencyPairInfoResponse implements Response
{
    public function __construct(
        public readonly string $base,
        public readonly string $quote,
        public readonly string $date,
        public readonly float $current,
        public readonly ?float $previous,
        public readonly ?string $delta,
        public readonly string $lastUpdatedAt
    ) {
    }

    public static function fromPair(CurrencyPairDynamicsProjection $pair): self
    {
        return new self(
            base: $pair->base,
            quote: $pair->quote,
            date: $pair->date,
            current: $pair->current,
            previous: $pair->previous,
            delta: $pair->delta,
            lastUpdatedAt: $pair->lastUpdatedAt
        );
    }
}
