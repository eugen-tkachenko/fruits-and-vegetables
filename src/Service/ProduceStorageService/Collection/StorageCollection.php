<?php

namespace App\Service\ProduceStorageService\Collection;

use Doctrine\Common\Collections\ArrayCollection;

class StorageCollection extends ArrayCollection
{
    /**
     * Method list() is added to fulfill the test requirements
     *
     * @return array
     */
    public function list(): array
    {
        return $this->toArray();
    }
}