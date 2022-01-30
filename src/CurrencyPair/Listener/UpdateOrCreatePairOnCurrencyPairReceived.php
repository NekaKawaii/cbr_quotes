<?php

declare(strict_types=1);

namespace App\CurrencyPair\Listener;

use App\CurrencyPair\CurrencyPair;
use App\CurrencyPair\CurrencyPairRepository;
use App\CurrencyPairsParser\Event\CurrencyPairReceived;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * When any of existing parser produces event about currency pair received, corresponding pair aggregate is created or updated
 */
final class UpdateOrCreatePairOnCurrencyPairReceived implements MessageSubscriberInterface
{
    public function __construct(private CurrencyPairRepository $currencyPairRepository)
    {
    }

    public function __invoke(CurrencyPairReceived $event): void
    {
        $pair = $this->currencyPairRepository->find(base: $event->base, quote: $event->quote);

        if ($pair !== null) {
            $pair->updateAmount(amount: $event->amount, date: $event->date);
        } else {
            $pair = CurrencyPair::create(base: $event->base, quote: $event->quote, amount: $event->amount, date: $event->date);
        }

        $this->currencyPairRepository->save($pair);
    }

    public static function getHandledMessages(): iterable
    {
        yield CurrencyPairReceived::class;
    }
}
