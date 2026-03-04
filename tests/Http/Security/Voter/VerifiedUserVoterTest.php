<?php

namespace App\Tests\Http\Security\Voter;

use App\Domain\Auth\Entity\User;
use App\Http\Security\Voter\VerifiedUserVoter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class VerifiedUserVoterTest extends TestCase
{
    #[DataProvider('voteProvider')]
    public function testVote(?bool $isConfirmed, int $expected): void
    {
        $token = $this->createStub(TokenInterface::class);

        if (null === $isConfirmed) {
            $token->method('getUser')->willReturn(null);
        } else {
            $user = $this->createStub(User::class);
            $user->method('isEmailConfirmed')->willReturn($isConfirmed);
            $token->method('getUser')->willReturn($user);
        }

        $voter = new VerifiedUserVoter();

        $result = $voter->vote($token, null, [VerifiedUserVoter::VERIFIED]);

        self::assertSame($expected, $result);
    }

    public static function voteProvider(): \Generator
    {
        yield 'anonymous' => [null, VoterInterface::ACCESS_DENIED];
        yield 'confirmed' => [true, VoterInterface::ACCESS_GRANTED];
        yield 'not confirmed user' => [false, VoterInterface::ACCESS_DENIED];
    }

    public function testItAbstainsOnUnsupportedAttribute(): void
    {
        $token = $this->createStub(TokenInterface::class);
        $token->method('getUser')->willReturn(null);

        $voter = new VerifiedUserVoter();

        $result = $voter->vote($token, null, ['WTF_SOMETHING_ELSE']);

        $this->assertSame(VoterInterface::ACCESS_ABSTAIN, $result);
    }
}
