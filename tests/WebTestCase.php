<?php

namespace App\Tests;

use App\Domain\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected ?SessionInterface $session = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        /** @var EntityManagerInterface $em */
        $emContainer = static::getContainer()->get(EntityManagerInterface::class);
        $this->em = $emContainer;
        $this->em->getConnection()->getConfiguration()->setMiddlewares([]);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->em->clear();
        parent::tearDown();
    }

    /**
     * To prepare a JSON response if you want to test an API.
     */
    public function jsonResponse(string $method, string $url, array $data = []): Response
    {
        $this->client->request($method, $url, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $data ? json_encode($data, JSON_THROW_ON_ERROR) : null);

        return $this->client->getResponse();
    }

    /**
     * To simulate a Csrf Token to test some method in POST.
     * Actuality It is not possible to get access to the session from tests.
     * You should to use the cookie and to write into a session file to simulate a session.
     */
    public function setCsrfToken(string $key): string
    {
        $csrf = uniqid();
        foreach ($this->client->getCookieJar()->all() as $cookie) {
            if ('MOCKSESSID' === $cookie->getName()) {
                $path = self::getContainer()->getParameter('kernel.cache_dir').'/sessions/'.$cookie->getValue().'.mocksess';
                $file = unserialize(file_get_contents($path));
                $file['_sf2_attributes']['_csrf/'.$key] = $csrf;
                file_put_contents($path, serialize($file));
            }
        }

        return $csrf;
    }

    /*
     * To get the session
     */
    protected function getSession(): SessionInterface
    {
        $this->ensureSessionIsInitialized();
        $this->client->request('GET', '/');

        return $this->client->getRequest()->getSession();
    }

    /**
     * To log in a user.
     */
    public function login(?User $user): void
    {
        if (null === $user) {
            return;
        }
        $this->client->loginUser($user);
    }

    /**
     * To count errors form validation.
     */
    public function expectedFormErrors(?int $expectedErrors = null): void
    {
        if (null === $expectedErrors) {
            $this->assertTrue($this->client->getCrawler()->filter('.form-error-message')->count() > 0, 'Form errors mismatched.');
        } else {
            $this->assertEquals($expectedErrors, $this->client->getCrawler()->filter('.form-error-message')->count(), 'Form errors mismatched.');
        }
    }

    /**
     * To initialise a session for the test environment.
     */
    private function ensureSessionIsInitialized(): void
    {
        $container = static::$kernel->getContainer();
        $requestStack = $container->get('request_stack');

        try {
            $requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            $session = $container->has('session') ?: $container->get('session.factory')->createSession();
            $masterRequest = new Request();
            $masterRequest->setSession($session);

            $requestStack->push($masterRequest);
            $session->start();
            $session->save();

            $cookie = new Cookie($session->getName(), $session->getId());
            $this->client->getCookieJar()->set($cookie);
        }
    }
}
