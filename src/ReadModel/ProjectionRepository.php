<?php

declare(strict_types=1);

namespace App\ReadModel;

use App\ReadModel\CurrencyPair\CurrencyPairDynamicsProjection;

interface ProjectionRepository
{
    public function find(string $base, string $quote): ?CurrencyPairDynamicsProjection;

    public function save(CurrencyPairDynamicsProjection $projection): void;
}
