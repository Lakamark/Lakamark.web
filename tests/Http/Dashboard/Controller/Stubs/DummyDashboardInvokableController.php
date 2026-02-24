<?php

namespace App\Tests\Http\Dashboard\Controller\Stubs;

use App\Http\Dashboard\Controller\BaseController;

/**
 * Test stub extending BaseController
 * to simulate a dashboard controller.
 *
 * Only used in test context.
 */
final class DummyDashboardInvokableController extends BaseController
{
    public function __invoke(): void
    {
    }
}
