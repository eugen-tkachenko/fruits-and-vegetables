<?php

namespace App\Service;

use App\DTO\ProduceDTO;
use App\Service\ProduceStorageService\DTO\DTOInterface;
use App\Service\ProduceStorageService\Enum\ProduceTypeEnum;
use App\Service\ProduceStorageService\Enum\UnitEnum;
use App\Service\ProduceStorageService\ProduceStorageService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class EnumProcessingTest extends KernelTestCase
{
    public function testProcessingRequest(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $validator = $container->get('validator');

        $request = file_get_contents('request.json');

        $storageService = new ProduceStorageService($request);

        $storageService->process($validator, ProduceDTO::class);

        $this->assertEquals(20, $storageService->getCollection()->count());

        $this->assertEquals(10, $storageService->getFruitsCollection()->count());

        $this->assertEquals(10, $storageService->getVegetableCollection()->count());

        foreach ($storageService->getCollection() as $produce) {
            $this->assertInstanceOf(DTOInterface::class, $produce);
            $this->assertEquals(UnitEnum::GRAM, $produce->getUnit());
        };

        foreach ($storageService->getFruitsCollection() as $produce) {
            $this->assertEquals(ProduceTypeEnum::FRUIT, $produce->getType());
        };

        foreach ($storageService->getVegetableCollection() as $produce) {
            $this->assertEquals(ProduceTypeEnum::VEGETABLE, $produce->getType());
        };
    }

}