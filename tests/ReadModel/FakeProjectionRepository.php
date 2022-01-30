<?php

declare(strict_types=1);

namespace App\Tests\ReadModel;

use App\ReadModel\CurrencyPair\CurrencyPairDynamicsProjection;
use App\ReadModel\ProjectionRepository;

/**
 * Fake projection repository for testing purposes
 */
final class FakeProjectionRepository implements ProjectionRepository
{
    /**
     * @var array<string, array<string, CurrencyPairDynamicsProjection>>
     */
    private array $projections = [];

    public function find(string $base, string $quote): ?CurrencyPairDynamicsProjection
    {
        return $this->projections[$base][$quote] ?? null;
    }

    public function save(CurrencyPairDynamicsProjection $projection): void
    {
        $this->projections[$projection->base][$projection->quote] = $projection;
    }
}
