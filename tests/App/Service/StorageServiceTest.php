<?php

namespace App\Tests\App\Service;

use App\DTO\ProduceDTO;
use App\Service\ProduceStorageService\ProduceStorageService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StorageServiceTest extends TestCase
{
    public function testReceivingRequest(): void
    {
        $request = file_get_contents('request.json');

        $storageService = new ProduceStorageService($request);

        $this->assertNotEmpty($storageService->getRequest());
        $this->assertIsString($storageService->getRequest());
    }
}
