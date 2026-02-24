<?php

namespace App\Http\Dashboard\Controller;

use App\Domain\Auth\Entity\User;
use App\Http\Controller\AbstractController;

/**
 * @method User|null getUser()
 */
abstract class BaseController extends AbstractController
{
}
