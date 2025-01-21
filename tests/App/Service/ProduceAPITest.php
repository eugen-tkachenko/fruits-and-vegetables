<?php

namespace App\Service;

use App\Service\ProduceStorageService\Enum\ProduceTypeEnum;
use App\Service\ProduceStorageService\Enum\UnitEnum;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProduceAPITest extends WebTestCase
{
    public function testIndexPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all');
        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertJson($response->getContent());
    }

    /**
     * Instead of running fixtures,
     * A latter test deletes all the produce from the test DB
     *
     * @return void
     */
    public function testLoadPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/load');
        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertJson($response->getContent());
        $this->assertResponseStatusCodeSame(201);
    }

    public function testQueryWithName(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all?name=ca');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);

        $this->assertCount(3, $array['data']);

        $this->assertEquals('Carrot', $array['data'][0]['name'] ?? null);
        $this->assertEquals('g', $array['data'][0]['unit'] ?? null);
        $this->assertEquals('10922', $array['data'][0]['quantity'] ?? null);
        $this->assertEquals('vegetable', $array['data'][0]['type'] ?? null);
    }

    public function testQueryWithType(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all?type=fruit');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);

        $this->assertCount(10, $array['data']);

        $this->assertEquals('Apples', $array['data'][0]['name'] ?? null);
        $this->assertEquals('g', $array['data'][0]['unit'] ?? null);
        $this->assertEquals('20000', $array['data'][0]['quantity'] ?? null);
        $this->assertEquals('fruit', $array['data'][0]['type'] ?? null);
    }

    public function testQueryWithNameAndType(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all?name=ca&type=fruit');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);

        $this->assertCount(1, $array['data']);

        $this->assertEquals('Avocado', $array['data'][0]['name'] ?? null);
        $this->assertEquals('g', $array['data'][0]['unit'] ?? null);
        $this->assertEquals('10000', $array['data'][0]['quantity'] ?? null);
        $this->assertEquals('fruit', $array['data'][0]['type'] ?? null);
    }

    public function testAvocadoInGrams(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all?name=ca&type=fruit');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);

        $this->assertCount(1, $array['data']);

        $this->assertEquals('Avocado', $array['data'][0]['name'] ?? null);
        $this->assertEquals('g', $array['data'][0]['unit'] ?? null);
        $this->assertEquals('10000', $array['data'][0]['quantity'] ?? null);
        $this->assertEquals('fruit', $array['data'][0]['type'] ?? null);
    }

    public function testAvocadoInKilograms(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all?name=ca&type=fruit&unit=kg');

        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();

        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);

        $this->assertCount(1, $array['data']);

        $this->assertEquals('Avocado', $array['data'][0]['name'] ?? null);
        $this->assertEquals('kg', $array['data'][0]['unit'] ?? null);
        $this->assertEquals('10', $array['data'][0]['quantity'] ?? null);
        $this->assertEquals('fruit', $array['data'][0]['type'] ?? null);
    }

    public function testNutsType(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all?type=nuts');

        $this->assertResponseStatusCodeSame(400);

        $response = $client->getResponse();

        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);

        $this->assertEquals(
            "'type' is not in: " . implode(', ', ProduceTypeEnum::values()),
            $array['error']
        );
    }

    public function testLbUnit(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/all?unit=lb');

        $this->assertResponseStatusCodeSame(400);

        $response = $client->getResponse();

        $this->assertJson($response->getContent());

        $array = json_decode($response->getContent(), true);

        $this->assertEquals(
            "'unit' is not in: " . implode(', ', UnitEnum::values()),
            $array['error']
        );
    }

    /**
     * For testing purposes only
     * Instead of resetting DB with the DAMA package,
     *
     * @return void
     */
    public function testResetPage(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/produce/reset');
        $this->assertResponseIsSuccessful();

        $response = $client->getResponse();


        $this->assertJson($response->getContent());
    }
}