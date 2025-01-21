<?php

namespace App\Tests\App\Service;

use App\Service\ProduceStorageService\Collection\StorageCollection;
use PHPUnit\Framework\TestCase;

class StorageCollectionTest extends TestCase
{
    public function testStorageCollectionHasMethods(): void
    {
        $collection = new StorageCollection();

        $this->assertTrue(
            method_exists($collection, 'add')
        );

        $this->assertTrue(
            method_exists($collection, 'remove')
        );

        $this->assertTrue(
            method_exists($collection, 'list')
        );
    }
}
