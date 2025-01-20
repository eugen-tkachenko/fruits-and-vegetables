<?php

namespace App\Entity;

/**
 * @TODO move to a standalone Type
 * @method static from
 * @method static tryFrom
 */
enum ProduceTypeEnum: string
{
    case FRUIT      = 'fruit';
    case VEGETABLE  = 'vegetable';

    public static function values(): array
    {
        return array_map(fn ($case) => $case->value, self::cases());
    }
}