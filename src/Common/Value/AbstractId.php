<?php
declare(strict_types=1);

namespace Common\Value;

use JsonSerializable;
use Ramsey\Uuid\Uuid;
use Stringable;
use Webmozart\Assert\Assert;

abstract class AbstractId implements Stringable, JsonSerializable
{
    public function __construct(private readonly string $value)
    {
    }

    public static function generate(): static
    {
        return new static((string) Uuid::uuid4());
    }

    public static function fromString(string $id): static
    {
        Assert::stringNotEmpty(trim($id));

        return new static($id);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
