<?php

namespace App\Service\ProduceStorageService\Enum;

/**
 * @TODO move to a standalone Type
 */
enum UnitEnum: string
{
    use ValuesEnumTrait;

    case GRAM      = 'g';
    case KILOGRAM  = 'kg';

    /**
     * Conversion from one certain unit to another can be ambiguous and buggy
     * Instead, one reference unit is chosen, GRAMS
     * Here, we only need to know conversion coefficients to and from (1/to) GRAMS only
     *
     * Feel free to add more units
     */
    public function getCoefficient(): float {
        return match ($this) {
            self::GRAM => 1,
            self::KILOGRAM => 1000,
        };
    }
}