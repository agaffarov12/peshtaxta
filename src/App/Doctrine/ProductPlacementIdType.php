<?php
declare(strict_types=1);

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Product\ProductPlacementId;
use UnexpectedValueException;

class ProductPlacementIdType extends Type
{
    const NAME = "product_placement_id";

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

        if (!$value instanceof ProductPlacementId) {
            throw ConversionException::conversionFailedInvalidType($value, "database", [ProductPlacementId::class, null]);
        }

        return (string) $value;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        try {
            return ProductPlacementId::fromString($value);
        } catch (UnexpectedValueException $exception) {
            throw ConversionException::conversionFailed($value, ProductPlacementId::class, $exception);
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
