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
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConstraintValidationTest extends KernelTestCase
{
    public function testIdConstraint(): void
    {
        $request = json_encode([[
            'uuid' => 1111111111111
        ]]);

        $this->exceptionTest(
            $request,
            \InvalidArgumentException::class,
            "'id' key is required"
        );
    }

    public function testNameConstraint(): void
    {
        $request = json_encode([[
            'id' => 1111111111111,
        ]]);

        $this->exceptionTest(
            $request,
            \InvalidArgumentException::class,
            "'name' key is required"
        );
    }

    public function testNameIsTooShortConstraint(): void
    {
        $request = json_encode([[
            'id' => 1111111111111,
            'name' => 'No', # error
            'quantity' => 1,
            'type' => 'fruit',
            'unit' => 'kg',
        ]]);

        $this->exceptionTest(
            $request,
            \InvalidArgumentException::class,
            "The 'name' is too short"
        );
    }


    public function testQuantityConstraint(): void
    {
        $request = json_encode([[
            'id' => 1111111111111,
            'name' => 'No',
        ]]);

        $this->exceptionTest(
            $request,
            \InvalidArgumentException::class,
            "'quantity' key is required"
        );
    }

    public function testTypeConstraint(): void
    {
        $request = json_encode([[
            'id' => 1111111111111,
            'name' => 'No',
            'quantity' => 1,
            'type' => 'nuts', # error
            'unit' => 'kg',
        ]]);

        $this->exceptionTest(
            $request,
            \InvalidArgumentException::class,
            "wrong 'type' option, available values are: " . implode(', ', ProduceTypeEnum::values())
        );
    }

    public function testUnitConstraint(): void
    {
        $request = json_encode([[
            'id' => 1111111111111,
            'name' => 'No',
            'quantity' => 1,
            'type' => 'fruit', # error
            'unit' => 'lb',
        ]]);

        $this->exceptionTest(
            $request,
            \InvalidArgumentException::class,
            "wrong 'unit' option, available values are: " . implode(', ', UnitEnum::values())
        );
    }

    public function exceptionTest($request, $exceptionClassString, $exceptionMessage): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $validator = $container->get('validator');

        $storageService = new ProduceStorageService($request);

        $this->expectException($exceptionClassString);

        try {
            $storageService->process($validator, ProduceDTO::class);
        } catch (\Exception $e) {

            $this->assertEquals($exceptionMessage, $e->getMessage());

            // in order to successfully fulfill the expectation that the test is going to fail
            throw $e;
        }
    }
}