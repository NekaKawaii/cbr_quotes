<?php

declare(strict_types=1);

namespace App\Tests\ReadModel\CurrencyPair;

use App\CurrencyPair\Event\CurrencyPairCreated;
use App\CurrencyPair\Event\CurrencyPairUpdated;
use App\ReadModel\CurrencyPair\CurrencyPairDynamicsProjection;
use App\ReadModel\Listener\CurrencyPairDynamicsProjectionManager;
use App\Tests\_tools\TestCase;
use App\Tests\ReadModel\FakeProjectionRepository;
use App\Type\Date;
use function App\now;

/**
 * Projections manager test suite
 *
 * @psalm-suppress MissingConstructor
 */
final class CurrencyPairDynamicsProjectionManagerTest extends TestCase
{
    private FakeProjectionRepository $repository;

    private CurrencyPairDynamicsProjectionManager $manager;

    /**
     * When currency pair created event emitted, projection must be created
     */
    public function testItCreatesProjectionOnCurrencyPairCreated(): void
    {
        $this->manager->__invoke(
            new CurrencyPairCreated(
                base: 'USD',
                quote: 'RUB',
                amount: 70.454,
                date: Date::create('2022-01-01'),
                occurredAt: now()
            )
        );

        $projection = $this->repository->find(base: 'USD', quote: 'RUB');

        self::assertNotNull($projection);
    }

    /**
     * Emitted event applies to already existing projection
     */
    public function testItAppliesEventOnAlreadyExistingProjection(): void
    {
        $this->repository->save(new CurrencyPairDynamicsProjection(
            base: 'USD',
            quote: 'RUB',
            date: '2022-01-01',
            current: 70.454,
            previous: null,
            delta: null,
            lastUpdatedAt: ''
        ));

        $this->manager->__invoke(
            new CurrencyPairUpdated(
                base: 'USD',
                quote: 'RUB',
                amount: 71.454,
                date: Date::create('2022-01-02'),
                yesterdayAmount: 70.454,
                occurredAt: now()
            )
        );

        $projection = $this->repository->find(base: 'USD', quote: 'RUB');

        self::assertNotNull($projection);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new FakeProjectionRepository();
        $this->manager = new CurrencyPairDynamicsProjectionManager($this->repository);
    }
}
