<?php

declare(strict_types=1);

namespace App\CurrencyPair;

use App\CurrencyPair\Event\AnomalyDetected;
use App\CurrencyPair\Event\CurrencyPairCreated;
use App\CurrencyPair\Event\CurrencyPairCurrentAmountUpdated;
use App\CurrencyPair\Event\CurrencyPairUpdated;
use App\CurrencyPair\Event\CurrencyPairYesterdayAmountReceived;
use App\Infrastructure\EventSourced;
use App\Type\Date;
use function App\now;

final class CurrencyPair extends EventSourced
{
    private function __construct(
        private string $base,
        private string $quote,
        private float $amount,
        private Date $date,
        private ?float $yesterdayAmount
    ) {
    }

    public static function create(string $base, string $quote, float $amount, Date $date): self
    {
        $pair = new self(base: $base, quote: $quote, amount: $amount, date: $date, yesterdayAmount: null);

        $pair->emit(new CurrencyPairCreated(base: $base, quote: $quote, amount: $amount, date: $date, occurredAt: now()));

        return $pair;
    }

    public function updateAmount(float $amount, Date $date): void
    {
        // Current amount is too old comparing to new data
        if ($date->isTooFarInTheFutureOf($this->date)) {
            $this->emit(new AnomalyDetected(
                base: $this->base,
                quote: $this->quote,
                description: 'Date with new amount is too far in the future',
                extra: [
                    'currentDate' => $this->date,
                    'newDate' => $date,
                    'newAmount' => $amount
                ],
                occurredAt: now()
            ));

            $this->emit(new CurrencyPairUpdated(
                base: $this->base,
                quote: $this->quote,
                amount: $amount,
                date: $date,
                yesterdayAmount: null, // Current amount is too old, it can not be yesterday's
                occurredAt: now()
            ));

            return;
        }

        // New amount is too old, throw it away
        if ($date->isTooFarInThePastOf($this->date)) {
            $this->emit(new AnomalyDetected(
                base: $this->base,
                quote: $this->quote,
                description: 'Date with new amount is too far in the past',
                extra: [
                    'currentDate' => $this->date,
                    'newDate' => $date,
                    'newAmount' => $amount
                ],
                occurredAt: now()
            ));

            return;
        }

        // Yesterday amount received
        if ($date->isDayBeforeOf($this->date)) {
            $this->emit(new CurrencyPairYesterdayAmountReceived(
                base: $this->base,
                quote: $this->quote,
                amount: $this->amount,
                date: $this->date,
                yesterdayAmount: $amount,
                occurredAt: now()
            ));

            return;
        }

        // Today's amount is updated
        if ($date->equals($this->date)) {
            $this->emit(new CurrencyPairCurrentAmountUpdated(
                base: $this->base,
                quote: $this->quote,
                amount: $amount,
                date: $date,
                occurredAt: now()
            ));
        }

        $this->emit(new CurrencyPairUpdated(
            base: $this->base,
            quote: $this->quote,
            amount: $amount,
            date: $date,
            yesterdayAmount: $this->amount,
            occurredAt: now()
        ));
    }

    /**
     * Update yesterday amount when it received
     */
    private function onCurrencyPairYesterdayAmountReceived(CurrencyPairYesterdayAmountReceived $event): void
    {
        $this->yesterdayAmount = $event->yesterdayAmount;
    }

    /**
     * Update currency pair amount with new data
     */
    private function onCurrencyPairUpdated(CurrencyPairUpdated $event): void
    {
        $this->amount = $event->amount;
        $this->yesterdayAmount = $event->yesterdayAmount;
    }

    /**
     * Set required fields when this currency pair is created
     */
    private function onCurrencyPairCreated(CurrencyPairCreated $event): void
    {
        $this->base = $event->base;
        $this->quote = $event->quote;
        $this->amount = $event->amount;
        $this->date = $event->date;
    }
}
