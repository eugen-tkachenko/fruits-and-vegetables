<?php

namespace App\Service\ProduceStorageService\Enum;

trait ValuesEnumTrait
{
    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}