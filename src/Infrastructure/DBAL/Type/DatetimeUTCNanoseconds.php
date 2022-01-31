<?php

namespace App\Infrastructure\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

class DatetimeUTCNanoseconds extends Type
{
    private const MICRO_FORMAT = 'Y-m-d H:i:s.u';

    /**
     * {@inheritdoc}
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TIMESTAMP(6)';
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return $value;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value->setTimezone(new \DateTimeZone('UTC'))->format(self::MICRO_FORMAT);
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', 'DateTimeImmutable']);
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null || $value instanceof \DateTimeImmutable) {
            return $value;
        }

        $val = \DateTimeImmutable::createFromFormat(self::MICRO_FORMAT, (string)$value);

        if (! $val) {
            $val = date_create_immutable((string)$value);
        }

        if (! $val) {
            throw ConversionException::conversionFailedFormat((string)$value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $val;
    }

    public function getName(): string
    {
        return 'datetime_utc_nanoseconds';
    }
}
