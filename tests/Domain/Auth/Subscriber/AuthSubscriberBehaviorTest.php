<?php

namespace App\Tests\Domain\Auth\Subscriber;

use App\Domain\Auth\DTO\IssuedTokenRequestDTO;
use App\Domain\Auth\Entity\TokenRequest;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\OAuthProvider;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Subscriber\AuthSubscriber;
use App\Foundation\Mailing\MailerBuilder;
use App\Foundation\Security\GeneratedTokenDTO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Mime\Email;

class AuthSubscriberBehaviorTest extends TestCase
{
    /**
     * @throws ExceptionInterface
     */
    public function testOnUserRegisteredSendsConfirmationEmail(): void
    {
        $user = (new User())
            ->setEmail('nomad@example.com')
            ->setUsername('Nomad');

        $tokenRequest = $this->createStub(TokenRequest::class);
        $tokenRequest
            ->method('getUser')
            ->willReturn($user);

        $issuedTokenRequest = new IssuedTokenRequestDTO(
            request: $tokenRequest,
            generated: new GeneratedTokenDTO(
                token: 'plain-token-123',
                hash: 'hashed-token-456',
            ),
        );

        $event = new UserRegisteredEvent($user, OAuthProvider::LOCAL, $issuedTokenRequest);

        $email = new Email();

        $mailerBuilder = $this->createMock(MailerBuilder::class);

        $mailerBuilder
            ->expects($this->once())
            ->method('buildEmail')
            ->with(
                'mails/auth/register.twig',
                [
                    'user' => $user,
                    'token' => 'plain-token-123',
                ]
            )
            ->willReturn($email);

        $mailerBuilder
            ->expects($this->once())
            ->method('deliveryEmail')
            ->with($this->callback(function (Email $sentEmail): bool {
                $to = $sentEmail->getTo();

                $this->assertCount(1, $to);
                $this->assertSame('nomad@example.com', $to[0]->getAddress());
                $this->assertSame('Laka Mark - Confirm your registration', $sentEmail->getSubject());

                return true;
            }));

        $subscriber = new AuthSubscriber($mailerBuilder);
        $subscriber->onUserRegistered($event);
    }
}
