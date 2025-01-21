<?php

namespace App\Service\ProduceStorageService;

use App\Service\ProduceStorageService\Collection\StorageCollection;
use App\Service\ProduceStorageService\DTO\DTOInterface;
use App\Service\ProduceStorageService\Enum\ProduceTypeEnum;
use App\Service\StorageService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProduceStorageService extends StorageService
{
    protected StorageCollection $collection;

    public function __construct(
        string $request
    )
    {
        parent::__construct($request);
        $this->collection = new StorageCollection();
    }

    /**
     * @param ValidatorInterface $validator
     * @param string $DTOClassString
     *
     * @throws \InvalidArgumentException
     *
     * @return StorageCollection
     */
    public function process(
        ValidatorInterface $validator,
        string $DTOClassString,
    ): StorageCollection
    {
        if ( ! in_array('App\Service\ProduceStorageService\DTO\DTOInterface', class_implements($DTOClassString)) ) {
            throw new \InvalidArgumentException("Class '$DTOClassString' does not implement DTOInterface'");
        }

        $elements = json_decode($this->request, true);

        # DTO validation
        foreach ($elements as $element) {

            $DTO = new $DTOClassString();

            $DTO->fillFromArray($element);

            $errors = $validator->validate($DTO);

            if (count($errors) > 0) {

                # so far, the first error only should be enough
                $errorString = $errors->get(0)->getMessage();

                $this->collection = new StorageCollection();

                throw new \InvalidArgumentException($errorString);
            }

            $this->collection->add($DTO);
        }

        return $this->collection;
    }

    public function getCollection(): StorageCollection
    {
        return $this->collection;
    }

    public function getFruitsCollection(): StorageCollection
    {
        return $this->filterByType(ProduceTypeEnum::FRUIT);
    }

    public function getVegetableCollection(): StorageCollection
    {
        return $this->filterByType(ProduceTypeEnum::VEGETABLE);
    }

    protected function filterByType(ProduceTypeEnum $enum): StorageCollection
    {
        return $this->collection->filter(fn($element) => $element->getType() == $enum);
    }
}
