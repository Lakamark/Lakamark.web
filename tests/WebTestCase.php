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
    protected ?SessionInterface $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        self::bootKernel();
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

    public function jsonResponse(string $method, string $url, array $data = []): Response
    {
        $this->client->request($method, $url, [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $data ? json_encode($data, JSON_THROW_ON_ERROR) : null);

        return $this->client->getResponse();
    }

    public function setCsrfToken(string $key): string
    {
        $csrf = uniqid();
        // Write directly into the session file cause there is no way to access the session from tests :(
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

    protected function getSession(): SessionInterface
    {
        $this->ensureSessionIsInitialized();
        $this->client->request('GET', '/');

        return $this->client->getRequest()->getSession();
    }

    public function login(?User $user): void
    {
        if (null === $user) {
            return;
        }
        $this->client->loginUser($user);
    }

    public function expectedFormErrors(?int $expectedErrors = null): void
    {
        if (null === $expectedErrors) {
            $this->assertTrue($this->client->getCrawler()->filter('.form-error')->count() > 0, 'Form errors mismatched.');
        } else {
            $this->assertEquals($expectedErrors, $this->client->getCrawler()->filter('.form-error')->count(), 'Form errors mismatched.');
        }
    }

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
