<?php

namespace App\Tests;

use App\Domain\Auth\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Request;
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
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $this->em = $em;
        $this->em->getConnection()->getConfiguration()->setMiddlewares([]);
    }

    protected function tearDown(): void
    {
        $this->em->clear();
        parent::tearDown();
    }

    public function expectLoginRedirect(): void
    {
        $this->assertResponseRedirects('/login');
    }

    /*
     * To simulate a logged user.
     */
    public function login(?User $user = null): void
    {
        if (null === $user) {
            return;
        }

        $this->client->loginUser($user);
    }

    public function setCsrfToken(string $key): string
    {
        $csrf = uniqid();

        // Write directly into the session file cause
        // at the moment we can get the session from tests :(
        foreach ($this->client->getCookieJar()->all() as $cookie) {
            if ('MOCKSESSID' === $cookie->getName()) {
                $path = self::getContainer()
                        ->getParameter('kernel.cache_dir').'/sessions/'.$cookie->getValue().'.mocksess';
                $file = unserialize(file_get_contents($path));
                $file['_sf2_attributes']['_csrf/'.$key] = $csrf;
                file_put_contents($path, serialize($file));
            }
        }

        return $csrf;
    }

    protected function getSession(): SessionInterface
    {
        $this->ensureSessionIsAvailable();
        $this->client->request('GET', '/');

        return $this->client->getRequest()->getSession();
    }

    private function ensureSessionIsAvailable(): void
    {
        $container = self::getContainer();
        $requestStack = $container->get('request_stack');

        try {
            $requestStack->getSession();
        } catch (SessionNotFoundException $e) {
            $session = $container->has('session')
                ? $container->get('session')
                : $container->get('session.factory')->createSession();

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
