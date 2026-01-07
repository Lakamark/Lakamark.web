<?php

namespace App\Tests\Domain\Application\Security;

use App\Domain\Application\Enum\AccessLevelEnum;
use App\Domain\Application\Enum\ContentStatusEnum;
use App\Domain\Application\Security\ContentAccessPolicy;
use App\Domain\Auth\Entity\User;
use App\Tests\Domain\Application\ContentFixture;
use App\Tests\Domain\Subscription\Gateway\FakeSubscriptionGateway;
use PHPUnit\Framework\TestCase;

class ContentAccessPolicyTest extends TestCase
{
    public function testMembersOnlyDeniedWhenNotLogged(): void
    {
        $policy = new ContentAccessPolicy(new FakeSubscriptionGateway(true));

        $author = new User();
        $content = new ContentFixture(AccessLevelEnum::PUBLIC, ContentStatusEnum::DRAFT, $author);

        $this->assertFalse($policy->canRead($content, null));
        $this->assertTrue($policy->canRead($content, $author));
        $this->assertFalse($policy->canRead($content, new User()));
    }

    public function testMembersOnlyPublishedRequiresSubscription(): void
    {
        $author = new User();
        $viewer = new User();
        $content = new ContentFixture(
            AccessLevelEnum::PREMIUM_MEMBER_ONLY,
            ContentStatusEnum::PUBLISHED,
            $author
        );

        $this->assertFalse((new ContentAccessPolicy(new FakeSubscriptionGateway(true)))->canRead($content, null));
        $this->assertFalse((new ContentAccessPolicy(new FakeSubscriptionGateway(false)))->canRead($content, $viewer));
        $this->assertTrue((new ContentAccessPolicy(new FakeSubscriptionGateway(true)))->canRead($content, $viewer));
    }

    public function testPrivatePublishedIsOnlyReadableByAuthor(): void
    {
        $policy = new ContentAccessPolicy(new FakeSubscriptionGateway(true));
        $author = new User();
        $content = new ContentFixture(
            AccessLevelEnum::PRIVATE,
            ContentStatusEnum::PUBLISHED,
            $author
        );

        $this->assertTrue($policy->canRead($content, $author));
        $this->assertFalse($policy->canRead($content, new User()));
        $this->assertFalse($policy->canRead($content, null));
    }
}
