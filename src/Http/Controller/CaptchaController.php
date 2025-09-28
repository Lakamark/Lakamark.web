<?php

namespace App\Http\Controller;

use App\Domain\Captcha\ChallengeGeneratorInterface;
use App\Domain\Captcha\ChallengeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    #[Route(path: '/captcha', name: 'captcha')]
    public function captcha(Request $request, ChallengeGeneratorInterface $challengeGenerator, ChallengeInterface $challenge): Response
    {
        $key = $challenge->generateKey();

        return $challengeGenerator->generate($key);
    }
}
