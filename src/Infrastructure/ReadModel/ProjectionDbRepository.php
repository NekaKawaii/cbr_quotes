<?php

declare(strict_types=1);

namespace App\Infrastructure\ReadModel;

use App\ReadModel\CurrencyPair\CurrencyPairDynamicsProjection;
use App\ReadModel\ProjectionRepository;
use Doctrine\DBAL\Connection;

final class ProjectionDbRepository implements ProjectionRepository
{
    public function __construct(private Connection $db)
    {
    }

    public function find(string $base, string $quote): ?CurrencyPairDynamicsProjection
    {
        $row = $this->db->fetchAssociative('
            SELECT base, quote, date, current, previous, delta, last_updated_at FROM currency_pair_projection
            WHERE base = ? AND quote = ?
        ', [$base, $quote]);

        if ($row === false) {
            return null;
        }

        if ($row['previous'] !== null) {
            $row['previous'] = (float)$row['previous'];
        }

        /** @psalm-suppress MixedArgument */
        return new CurrencyPairDynamicsProjection(
            base: $row['base'],
            quote: $row['quote'],
            date: $row['date'],
            current: (float)$row['current'],
            previous: $row['previous'],
            delta: $row['delta'],
            lastUpdatedAt: $row['last_updated_at']
        );
    }

    public function save(CurrencyPairDynamicsProjection $projection): void
    {
        $this->db->executeStatement('
            INSERT INTO currency_pair_projection (base, quote, date, current, previous, delta, last_updated_at) VALUES
            (:base, :quote, :date, :current, :previous, :delta, :last_updated_at)
            ON CONFLICT (base, quote)
                DO UPDATE SET date = :date, current = :current, previous = :previous, delta = :delta, last_updated_at = :last_updated_at
        ', [
            'base' => $projection->base,
            'quote' => $projection->quote,
            'date' => $projection->date,
            'current' => $projection->current,
            'previous' => $projection->previous,
            'delta' => $projection->delta,
            'last_updated_at' => $projection->lastUpdatedAt,
        ]);
    }
}
