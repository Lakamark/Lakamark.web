<?php

namespace App\Http\Controller;

use App\Foundation\Captcha\CaptchaService;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CaptchaController extends AbstractController
{
    public function __construct(
        private readonly CaptchaService $captcha,
    ) {
    }

    /**
     * Generate a captcha challenge.
     */
    #[Route(
        path: '/captcha/generate/{type}',
        name: 'app_captcha_generate',
        requirements: ['type' => 'puzzle'],
        methods: ['GET']
    )]
    public function generate(string $type): Response
    {
        return $this->captcha->generate($type);
    }

    /**
     * Verify a captcha answer.
     */
    #[Route(
        path: '/captcha/verify',
        name: 'app_captcha_verify',
        methods: ['POST']
    )]
    public function verify(Request $request): JsonResponse
    {
        if (!$this->isJsonRequest($request)) {
            return $this->json(
                ['error' => 'only application/json allowed'],
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE
            );
        }

        try {
            $payload = json_decode(
                (string) $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            return $this->json(
                ['valid' => false, 'error' => 'invalid_json'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!is_array($payload)) {
            return $this->json(
                ['valid' => false, 'error' => 'invalid_payload'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $type = $payload['type'] ?? null;
        $answer = $payload['answer'] ?? null;
        $challenge = $payload['challenge'] ?? null;

        if (!is_string($type) || !is_string($answer)) {
            return $this->json(
                ['valid' => false, 'error' => 'invalid_payload'],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $valid = $this->captcha->verify($type, $answer, $challenge);
        } catch (CaptchaLockedException) {
            return $this->json([
                'valid' => false,
                'locked' => true,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        return $this->json([
            'valid' => $valid,
        ]);
    }

    private function isJsonRequest(Request $request): bool
    {
        return str_starts_with(
            (string) $request->headers->get('Content-Type'),
            'application/json'
        );
    }
}
