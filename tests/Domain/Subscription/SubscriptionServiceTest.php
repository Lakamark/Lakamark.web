<?php

namespace App\Tests\Domain\Subscription;

use App\Domain\Subscription\Service\SubscriptionService;
use App\Tests\ServiceTestCase;

/**
 * @extends ServiceTestCase<SubscriptionService>
 */
class SubscriptionServiceTest extends ServiceTestCase
{
    protected string $serviceClass = SubscriptionService::class;

    public function testTrueReturnTrue(): void
    {
        $this->assertTrue(true);
    }
}
