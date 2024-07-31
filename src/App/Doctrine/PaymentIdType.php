<?php
declare(strict_types=1);

namespace App\Doctrine;

use Campaign\PaymentId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use UnexpectedValueException;

class PaymentIdType extends Type
{
    const NAME = "payment_id";

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

        if (!$value instanceof PaymentId) {
            throw ConversionException::conversionFailedInvalidType($value, "database", [PaymentId::class, null]);
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        try {
            return PaymentId::fromString($value);
        } catch (UnexpectedValueException $exception) {
            throw ConversionException::conversionFailed($value, PaymentId::class, $exception);
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
