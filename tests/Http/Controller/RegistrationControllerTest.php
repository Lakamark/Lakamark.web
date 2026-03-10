<?php

namespace App\Tests\Http\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Exception\TokenRequest\ConsumedTokenException;
use App\Domain\Auth\Service\ConfirmAccountService;
use App\Domain\Auth\Service\TokenRequestService;
use App\Tests\FixturesLoaderTrait;
use App\Tests\WebTestCase;
use Random\RandomException;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    use FixturesLoaderTrait;

    private const string SIGNUP_ROUTE = '/register';
    private const string CONFIRM_ROUTE = '/register/confirm';
    private const string LOGIN_ROUTE = '/login';
    private const string SIGNUP_BTN = 'Register';

    public function testGetRegisterPage(): void
    {
        $crawler = $this->client->request('GET', self::SIGNUP_ROUTE);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.h1', 'Register');
        $this->assertSelectorExists('form');

        $form = $crawler->selectButton(self::SIGNUP_BTN)->form();
        $this->assertNotNull($form);
    }

    public function testRegisterWithInvalidForm(): void
    {
        $crawler = $this->client->request('GET', self::SIGNUP_ROUTE);

        $form = $crawler->selectButton(self::SIGNUP_BTN)->form();
        $form->setValues([
            'registration_form' => [
                'username' => '',
                'email' => '',
                'plainPassword' => '',
            ],
        ]);

        $this->client->submit($form);

        $statusCode = $this->client->getResponse()->getStatusCode();

        $this->assertContains($statusCode, [
            Response::HTTP_OK,
            Response::HTTP_UNPROCESSABLE_ENTITY,
        ]);

        $this->assertSelectorExists('form');
        $this->expectedFormErrors();
    }

    public function testRegisterWithValidForm(): void
    {
        $crawler = $this->client->request('GET', self::SIGNUP_ROUTE);

        $form = $crawler->selectButton(self::SIGNUP_BTN)->form();
        $form->setValues([
            'registration_form' => [
                'username' => 'JohnDoe',
                'email' => 'john@do.com',
                'plainPassword' => 'password123',
            ],
        ]);

        $this->client->submit($form);

        $this->expectLoginRedirect();
    }

    /**
     * @throws RandomException
     */
    public function testConfirmAccountWithValidTokenRedirectsToLogin(): void
    {
        $user = (new User())
            ->setEmail('confirm@example.com')
            ->setUsername('ConfirmUser')
            ->setPassword('hashed-password')
            ->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($user);
        $this->em->flush();

        /** @var TokenRequestService $tokenRequestService */
        $tokenRequestService = self::getContainer()->get(TokenRequestService::class);

        $issued = $tokenRequestService->issue(
            user: $user,
            type: TokenRequestType::REGISTER_CONFIRMATION,
        );

        $this->client->request(
            'GET',
            self::CONFIRM_ROUTE.'?token='.$issued->getToken()
        );

        $this->expectLoginRedirect();
    }

    public function testConfirmAccountWithInvalidTokenRedirectsToRegister(): void
    {
        $this->client->request(
            'GET',
            self::CONFIRM_ROUTE.'?token=invalid-token'
        );

        $this->assertResponseRedirects(self::LOGIN_ROUTE);
    }

    public function testConfirmAccountWithoutTokenRedirectsToRegister(): void
    {
        $this->client->request('GET', self::CONFIRM_ROUTE);

        $this->assertResponseRedirects(self::LOGIN_ROUTE);
    }

    /**
     * @throws RandomException
     */
    public function testConfirmThrowsExceptionWhenTokenAlreadyConsumed(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy([]);
        $this->assertInstanceOf(User::class, $user);

        $userId = $user->getId();

        $tokenRequestService = $this->service(TokenRequestService::class);
        $issued = $tokenRequestService->issue(
            user: $user,
            type: TokenRequestType::REGISTER_CONFIRMATION,
        );

        $service = $this->service(ConfirmAccountService::class);

        $service->confirm($issued->getToken());

        $this->em->clear();

        $savedUser = $this->em->getRepository(User::class)->find($userId);

        $this->assertInstanceOf(User::class, $savedUser);
        $this->assertNotNull($savedUser->getConfirmAt());

        $this->expectException(ConsumedTokenException::class);
        $service->confirm($issued->getToken());
    }
}
