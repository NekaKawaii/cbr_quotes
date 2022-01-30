<?php

declare(strict_types=1);

namespace App\Tests\CurrencyPair;

use App\CurrencyPair\CurrencyPair;
use App\CurrencyPair\Event\AnomalyDetected;
use App\CurrencyPair\Event\CurrencyPairCreated;
use App\CurrencyPair\Event\CurrencyPairCurrentAmountUpdated;
use App\CurrencyPair\Event\CurrencyPairUpdated;
use App\CurrencyPair\Event\CurrencyPairYesterdayAmountReceived;
use App\Tests\_tools\TestCase;
use App\Type\Date;
use function App\now;

/**
 * @psalm-suppress MissingConstructor
 */
final class CurrencyPairTest extends TestCase
{
    private CurrencyPair $pair;

    /**
     * Currency pair should emit event about its creation
     */
    public function testItEmitEventOnCreation(): void
    {
        $pair = CurrencyPair::create(base: 'USD', quote: 'RUB', amount: 70.454, date: Date::create('2022-01-01'));

        self::assertObjectHasEventInStream(
            eventSourced: $pair,
            event: new CurrencyPairCreated(base: 'USD', quote: 'RUB', amount: 70.454, date: Date::create('2022-01-01'), occurredAt: now())
        );
    }

    /**
     * Currency pair can be updated with new amount on the next day
     */
    public function testItUpdatesWithNewAmount(): void
    {
        $this->pair->updateAmount(amount: 77.33, date: Date::create('2022-01-02'));

        self::assertObjectHasEventInStream(
            eventSourced: $this->pair,
            event: new CurrencyPairUpdated(
                base: 'USD',
                quote: 'RUB',
                amount: 77.33,
                date: Date::create('2022-01-02'),
                yesterdayAmount: 70.454,
                occurredAt: now()
            )
        );
    }

    /**
     * When date of new data is too far in the future, it should be detected, but amount still should be updated
     */
    public function testItDetectsAnomalyWhenNewDateIsTooFarInTheFutureButUpdatesAmount(): void
    {
        $this->pair->updateAmount(amount: 100.27, date: Date::create('2022-10-20'));

        // Anomaly detected
        self::assertObjectHasEventInStream(
            eventSourced: $this->pair,
            event: new AnomalyDetected(
                base: 'USD',
                quote: 'RUB',
                description: 'Date with new amount is too far in the future',
                extra: [
                    'currentDate' => Date::create('2022-01-01'),
                    'newDate' => Date::create('2022-10-20'),
                    'newAmount' => 100.27
                ],
                occurredAt: now()
            )
        );

        // But amount is updated
        self::assertObjectHasEventInStream(
            eventSourced: $this->pair,
            event: new CurrencyPairUpdated(
                base: 'USD',
                quote: 'RUB',
                amount: 100.27,
                date: Date::create('2022-10-20'),
                yesterdayAmount: null,
                occurredAt: now()
            )
        );
    }

    /**
     * When date of new data is too far in the past, it should be detected and amount should not be changed
     */
    public function testItDetectsAnomalyWhenNewDateIsTooFarInThePastAndAmountIsNotChanged(): void
    {
        $this->pair->updateAmount(amount: 30.15, date: Date::create('2013-10-20'));

        // It should not be updated
        self::assertObjectHasNoEventInStream(eventSourced: $this->pair, eventClass: CurrencyPairUpdated::class);

        // And anomaly detected
        self::assertObjectHasEventInStream(
            eventSourced: $this->pair,
            event: new AnomalyDetected(
                base: 'USD',
                quote: 'RUB',
                description: 'Date with new amount is too far in the past',
                extra: [
                    'currentDate' => Date::create('2022-01-01'),
                    'newDate' => Date::create('2013-10-20'),
                    'newAmount' => 30.15
                ],
                occurredAt: now()
            )
        );
    }

    /**
     * Yesterday amount can be set to the currency pair
     */
    public function testItSetYesterdayAmountWhenOnUpdatingWithYesterdaysDate(): void
    {
        $this->pair->updateAmount(amount: 74.12, date: Date::create('2021-12-31'));

        self::assertObjectHasEventInStream(
            eventSourced: $this->pair,
            event: new CurrencyPairYesterdayAmountReceived(
                base: 'USD',
                quote: 'RUB',
                amount: 70.454,
                date: Date::create('2022-01-01'),
                yesterdayAmount: 74.12,
                occurredAt: now()
            )
        );
    }

    /**
     * Current amount (today amount) can be updated
     */
    public function testItSetNewAmountOnNewDataOnTheSameDay(): void
    {
        $this->pair->updateAmount(amount: 85.12, date: Date::create('2022-01-01'));

        self::assertObjectHasEventInStream(
            eventSourced: $this->pair,
            event: new CurrencyPairCurrentAmountUpdated(
                base: 'USD',
                quote: 'RUB',
                amount: 85.12,
                date: Date::create('2022-01-01'),
                occurredAt: now()
            )
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->pair = CurrencyPair::create(base: 'USD', quote: 'RUB', amount: 70.454, date: Date::create('2022-01-01'));
    }
}
