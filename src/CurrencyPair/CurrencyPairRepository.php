<?php

declare(strict_types=1);

namespace App\CurrencyPair;

/**
 * Repository for currency pair aggregates
 */
interface CurrencyPairRepository
{
    /**
     * Find currency pair by base and quote currency codes
     */
    public function find(string $base, string $quote): ?CurrencyPair;

    /**
     * Save currency pair
     */
    public function save(CurrencyPair $pair): void;
}
