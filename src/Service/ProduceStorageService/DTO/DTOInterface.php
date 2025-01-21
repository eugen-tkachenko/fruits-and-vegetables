<?php

namespace App\Service\ProduceStorageService\DTO;

interface DTOInterface
{
    public function fillFromArray(array $array): static;
}