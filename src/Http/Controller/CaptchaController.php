<?php

namespace App\Http\Controller;

use App\Foundation\Captcha\CaptchaService;
use App\Foundation\Captcha\Exception\CaptchaLockedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CaptchaController extends AbstractController
{
    public function __construct(
        private readonly CaptchaService $captcha,
    ) {
    }

    /**
     * Returns the captcha image and stores (type/key/tries) in session.
     */
    #[Route(path: '/captcha/{type}', name: 'app_captcha', methods: ['GET'])]
    public function index(string $type): Response
    {
        return $this->captcha->generate($type);
    }

    /**
     * Verify the captcha answer.
     * Accepts JSON {"answer":"..."} or form-data answer=...
     */
    #[Route(path: '/captcha/verify', name: 'app_captcha_verify', methods: ['POST'])]
    public function verify(Request $request): JsonResponse
    {
        // We accept only JSON format.
        if ($this->isValidJSONHeader($request)) {
            return $this->json(
                ['error' => 'only application/json allowed'],
                Response::HTTP_UNSUPPORTED_MEDIA_TYPE
            );
        }

        // Check the payload

        $payload = json_decode($request->getContent(), true);

        if (!is_array($payload)) {
            return $this->json([
                'valid' => false,
                'error' => 'Invalid JSON',
            ], Response::HTTP_BAD_REQUEST);
        }

        $answer = $payload['answer'] ?? null;

        if (!is_string($answer) || '' === $answer) {
            return $this->json([
                'valid' => false,
                'error' => 'invalid_payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        // check the payload.
        try {
            $payload = json_decode(
                (string) $request->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException) {
            $this->json(
                ['valid' => false, 'error' => 'Invalid JSON'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $answer = $payload['answer'] ?? null;
        if (!is_string($answer) || '' === $answer) {
            return $this->json([
                'valid' => false,
                'error' => 'invalid_payload',
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $valid = $this->captcha->verify($answer);
        } catch (CaptchaLockedException) {
            return $this->json([
                'valid' => false,
                'locked' => true,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        return $this->json(['valid' => $valid]);
    }

    private function isValidJSONHeader(Request $request): bool
    {
        return !str_starts_with((string) $request->headers->get('Content-Type'), 'application/json');
    }
}
