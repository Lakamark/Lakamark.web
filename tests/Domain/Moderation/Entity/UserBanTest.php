<?php

namespace App\Tests\Domain\Moderation\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Entity\UserBan;
use App\Domain\Moderation\Enum\BanReasonEnum;
use PHPUnit\Framework\TestCase;

class UserBanTest extends TestCase
{
    public function testBanIsActiveWhenEndedAtIsNull(): void
    {
        $ban = (new UserBan())
            ->setUser(new User())
            ->setBanReason(BanReasonEnum::BOT)
            ->setEndedAt(null);

        $this->assertTrue($ban->isActive());
    }

    public function testBanIsUnActiveWhenEndedAtIsNotNull(): void
    {
        $ban = (new UserBan())
            ->setUser(new User())
            ->setBanReason(BanReasonEnum::TERMS_VIOLATION)
            ->setEndedAt(new \DateTimeImmutable('yesterday'));

        $this->assertFalse($ban->isActive());
    }
}
