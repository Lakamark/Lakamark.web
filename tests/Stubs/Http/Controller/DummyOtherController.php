<?php

namespace App\Tests\Stubs\Http\Controller;

/**
 * Generic invokable test controller.
 *
 * Does not extend BaseController.
 * Can be reused in multiple HTTP-level tests.
 */
final class DummyOtherController
{
    public function index(): void
    {
    }
}
