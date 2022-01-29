<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;

/**
 * Returns DateTimeImmutable with current date and time with microseconds
 *
 * @psalm-pure
 */
function now(): DateTimeImmutable
{
    /**
     * @var DateTimeImmutable $now
     * @psalm-suppress ImpureFunctionCall
     * @noinspection PhpUnnecessaryLocalVariableInspection
     */
    $now = DateTimeImmutable::createFromFormat('0.u00 U', \microtime());

    return $now;
}
