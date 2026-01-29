<?php

namespace App\Http\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\RememberMeToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InsufficientAuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

readonly class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private AccessDeniedHandler $accessDeniedHandler,
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $previous = $authException?->getPrevious();

        if (
            $authException instanceof InsufficientAuthenticationException
            && $previous instanceof AccessDeniedException
            && $authException->getToken() instanceof RememberMeToken
        ) {
            return $this->accessDeniedHandler->handle($request, $previous);
        }

        // If the request is executed from an API
        // we return a JSON response.
        if (in_array('application/json', $request->getAcceptableContentTypes())) {
            return new JsonResponse([
                'title' => "You're not allowed to access this resource.",
                Response::HTTP_FORBIDDEN,
            ]);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
