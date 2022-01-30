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

/**
 * Get short name of the class of the object or directly from FQCN class string
 *
 * @psalm-param class-string|object $argument
 *
 * @throws \ReflectionException
 */
function getClassShortName($argument): string
{
    // Getting shortname using reflection is faster than FQCN string manipulation
    return (new \ReflectionClass($argument))->getShortName();
}

/**
 * Retrieve private or protected property value of object
 * SHOULD NOT be used in domain logic (can be restricted in the architectural checker rules)
 *
 * @psalm-param class-string|null $class Parent class in the hierarchy of instance where property is defined
 *
 * @return mixed|null
 *
 * @throws \ReflectionException
 */
function getPropertyValueByReflection(object $object, string $propertyName, ?string $class = null)
{
    $propertyRef = (new \ReflectionClass($class === null ? get_class($object) : $class))->getProperty($propertyName);

    /** @noinspection PhpExpressionResultUnusedInspection */
    $propertyRef->setAccessible(true);

    return $propertyRef->getValue($object);
}

/**
 * Set new value for private or protected or read-only property of object
 * SHOULD NOT be used in domain logic (can be restricted in the architectural checker rules)
 *
 * @param mixed|null $value
 * @psalm-param class-string|null $class Parent class in the hierarchy of instance where property is defined
 *
 * @throws \ReflectionException
 */
function setPropertyValueByReflection(object $object, string $propertyName, $value, ?string $class = null): void
{
    $propertyReflected = (new \ReflectionClass(
        $class !== null ? $class : \get_class($object)
    ))->getProperty($propertyName);

    /** @noinspection PhpExpressionResultUnusedInspection */
    $propertyReflected->setAccessible(true);
    $propertyReflected->setValue($object, $value);
}
