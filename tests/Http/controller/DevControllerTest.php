<?php

namespace App\Tests\Http\controller;

use App\Tests\WebTestCase;

class DevControllerTest extends WebTestCase
{
    public function testShowPage(): void
    {
        $crawler = $this->client->request('GET', '/dev');
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('Dev - Laka Mark', $crawler->filter('title')->text());
    }

    public function testShowPageOnProd(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient([
            'debug' => false,
        ]);

        $this->client->request('GET', '/dev');

        $this->assertResponseRedirects('/');
    }

    public function testShowPageOnProdAsJson(): void
    {
        self::ensureKernelShutdown();

        $this->client = static::createClient([
            'debug' => false,
        ]);

        $this->client->request('GET', '/dev', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $this->assertJsonStringEqualsJsonString(
            json_encode([
                'error' => 'You are not allowed to access this endpoint.',
            ]),
            $this->client->getResponse()->getContent()
        );

        $this->assertResponseStatusCodeSame(403);
    }
}
