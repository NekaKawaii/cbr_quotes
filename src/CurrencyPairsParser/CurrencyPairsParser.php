<?php

declare(strict_types=1);

namespace App\CurrencyPairsParser;

use App\CurrencyPairsParser\Event\CurrencyPairReceived;
use App\Type\Date;

/**
 * Interface for currency pairs parser
 */
interface CurrencyPairsParser
{
    /**
     * Parse data from third party and emit event about each parsed currency pair to the message bus
     *
     * @see CurrencyPairReceived
     */
    public function parse(Date $date): void;
}
