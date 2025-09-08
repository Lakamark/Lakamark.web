<?php

namespace App\Tests\Http\Controller;

use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    private const SIGNUP_PATH = '/register';
    private const CONFIRM_PATH = '/register/confirmation/';
    private const SIGNUP_BTN = 'Register';

    public function testDisplayRegistrationPage(): void
    {
        $crawler = $this->client->request('GET', self::SIGNUP_PATH);
        $this->assertResponseStatusCodeSame(200);
        $content = $crawler->filter('.h1')->text();
        $this->assertEquals('Register', $content);
    }
}
