<?php

namespace App\Tests\Controller\API;

use App\Tests\DatabaseTestCase;

final class BarberControllerTest extends DatabaseTestCase
{
    public function testGetByBarbershop(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/barbers/by-barbershop/1');

        self::assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        self::assertIsArray($data);
    }
}
