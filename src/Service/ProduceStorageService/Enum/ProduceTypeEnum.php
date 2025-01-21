<?php

namespace App\Service\ProduceStorageService\Enum;

/**
 * @TODO move to a standalone Type
 */
enum ProduceTypeEnum: string
{
    use ValuesEnumTrait;

    case FRUIT      = 'fruit';
    case VEGETABLE  = 'vegetable';

}