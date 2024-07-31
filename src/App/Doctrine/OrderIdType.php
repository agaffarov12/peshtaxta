<?php
declare(strict_types=1);

namespace App\Doctrine;

use Campaign\OrderId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use UnexpectedValueException;

class OrderIdType extends Type
{
    const NAME = "order_id";

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL($column);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!$value instanceof OrderId) {
            throw ConversionException::conversionFailedInvalidType($value, "database", [OrderId::class, null]);
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        try {
            return OrderId::fromString($value);
        } catch (UnexpectedValueException $exception) {
            throw ConversionException::conversionFailed($value, OrderId::class, $exception);
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
