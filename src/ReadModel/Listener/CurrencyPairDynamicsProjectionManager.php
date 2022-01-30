<?php

declare(strict_types=1);

namespace App\ReadModel\Listener;

use App\CurrencyPair\Event\CurrencyPairCreated;
use App\CurrencyPair\Event\CurrencyPairCurrentAmountUpdated;
use App\CurrencyPair\Event\CurrencyPairUpdated;
use App\CurrencyPair\Event\CurrencyPairYesterdayAmountReceived;
use App\ReadModel\CurrencyPair\CurrencyPairDynamicsProjection;
use App\ReadModel\ProjectionRepository;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use function App\createObjectWithoutConstructor;
use function App\getClassShortName;

/**
 * Manager for currency pair projections
 */
final class CurrencyPairDynamicsProjectionManager implements MessageSubscriberInterface
{
    public function __construct(private ProjectionRepository $repository)
    {
    }

    public function __invoke(
        CurrencyPairCreated|CurrencyPairUpdated|CurrencyPairYesterdayAmountReceived|CurrencyPairCurrentAmountUpdated $event
    ): void {
        $projection = $this->repository->find($event->base, $event->quote);

        if ($projection === null) {
            $projection = createObjectWithoutConstructor(CurrencyPairDynamicsProjection::class);
        }

        $this->applyEventToProjection($event, $projection);

        $this->repository->save($projection);
    }

    private function applyEventToProjection(object $event, CurrencyPairDynamicsProjection $projection): void
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $methodName = \sprintf('on%s', getClassShortName($event));

        if (\method_exists($projection, $methodName) !== true) {
            return;
        }

        /** @psalm-suppress MissingClosureReturnType */
        (fn (object $event) => $this->{$methodName}($event))
            ->call($projection, $event);
    }

    public static function getHandledMessages(): iterable
    {
        yield CurrencyPairCreated::class;
        yield CurrencyPairUpdated::class;
        yield CurrencyPairYesterdayAmountReceived::class;
        yield CurrencyPairCurrentAmountUpdated::class;
    }
}
