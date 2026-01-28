<?php

namespace App\Tests\Http\controller;

use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    public function testHomePage(): void
    {
        $this->loadFixtures(['users']);
        $crawler = $this->client->request('GET', '/');
        $this->assertResponseStatusCodeSame(200);
        $this->assertEquals('HomePage - Laka Mark', $crawler->filter('title')->text());
    }
}
