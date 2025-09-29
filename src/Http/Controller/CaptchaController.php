<?php

namespace App\Http\Controller;

use App\Domain\Captcha\CaptchaGeneratorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    #[Route(path: '/captcha', name: 'captcha', methods: ['GET'])]
    public function captcha(Request $request, CaptchaGeneratorInterface $captchaGenerator,
    ): Response {
        
        return $captchaGenerator->generate($request->query->get('challenge', ''));
    }
}
