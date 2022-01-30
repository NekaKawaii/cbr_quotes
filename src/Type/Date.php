<?php

declare(strict_types=1);

namespace App\Type;

use function App\now;

/**
 * Date without time.
 */
final class Date
{
    private function __construct(private string $dateString)
    {
    }

    /**
     * Create from date string with format Y-m-d
     */
    public static function create(string $dateString): self
    {
        if (\DateTimeImmutable::createFromFormat('Y-m-d', $dateString) === false) {
            throw new \RuntimeException('Wrong date format');
        }

        return new self($dateString);
    }

    /**
     * Today's date
     */
    public static function today(): self
    {
        return new self(now()->format('Y-m-d'));
    }

    /**
     * Yesterday's date
     */
    public static function yesterday(): self
    {
        return new self(now()->modify('-1 day')->format('Y-m-d'));
    }

    /**
     * Is other date after tomorrow?
     */
    public function isTooFarInTheFutureOf(Date $other): bool
    {
        $interval = $this->toDateTime($other)->diff($this->toDateTime($this));

        return $interval->days > 1 && $interval->invert === 0;
    }

    /**
     * Is other date before yesterday?
     */
    public function isTooFarInThePastOf(Date $other): bool
    {
        $interval = $this->toDateTime($other)->diff($this->toDateTime($this));

        return $interval->days > 1 && $interval->invert === 1;
    }

    /**
     * Is other date the day after current (tomorrow for current)&
     */
    public function isDayBeforeOf(Date $other): bool
    {
        $interval = $this->toDateTime($other)->diff($this->toDateTime($this));

        return $interval->days === 1 && $interval->invert === 1;
    }

    /**
     * Is dates equal?
     */
    public function equals(Date $other): bool
    {
        return $this->dateString === $other->dateString;
    }

    /**
     * Convert date to custom format
     *
     * @see date() for format examples
     */
    public function format(string $format): string
    {
        return $this->toDateTime($this)->format($format);
    }

    public function __toString(): string
    {
        return $this->dateString;
    }

    /**
     * @psalm-suppress InvalidFalsableReturnType
     */
    private function toDateTime(self $date): \DateTimeImmutable
    {
        /** @psalm-suppress FalsableReturnStatement */
        return \DateTimeImmutable::createFromFormat('Y-m-d', $date->dateString);
    }
}
