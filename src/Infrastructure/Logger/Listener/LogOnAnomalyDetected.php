<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger\Listener;

use App\CurrencyPair\Event\AnomalyDetected;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;

/**
 * Log any anomaly which was detected
 */
final class LogOnAnomalyDetected implements MessageSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function __invoke(AnomalyDetected $event)
    {
        $this->logger->notice("Anomaly Detected: $event->description", $event->extra);
    }

    public static function getHandledMessages(): iterable
    {
        yield AnomalyDetected::class;
    }
}
