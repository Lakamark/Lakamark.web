<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class Authenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const string LOGIN_ROUTE = 'app_login';
    private const string FIELD_IDENTIFIER = 'username';
    private const string FIELD_PASSWORD = 'password';
    private const string FIELD_CSRF = '_csrf_token';

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly UrlMatcherInterface $urlMatcher,
    ) {
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    public function authenticate(Request $request): Passport
    {
        $data = $request->request->all();

        $identifier = mb_strtolower(trim((string) ($data[self::FIELD_IDENTIFIER] ?? '')), 'UTF-8');
        $password = (string) ($data[self::FIELD_PASSWORD] ?? '');
        $csrf = (string) ($data[self::FIELD_CSRF] ?? '');

        if ($request->hasSession()) {
            $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $identifier);
        }

        return new Passport(
            new UserBadge($identifier, fn (string $id) => $this->userRepository->findByUsernameIdentifier($id)),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $csrf),
                new RememberMeBadge(),
            ],
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($redirect = $request->query->get('redirect')) {
            try {
                $this->urlMatcher->match($redirect);

                return new RedirectResponse($redirect);
            } catch (\Exception) {
                // ignore invalid redirect
            }
        }

        if ($request->hasSession() && ($targetPath = $this->getTargetPath($request->getSession(), $firewallName))) {
            // Prevent redirect loop to log in
            if ($targetPath !== $this->getLoginUrl($request)) {
                return new RedirectResponse($targetPath);
            }
            $this->removeTargetPath($request->getSession(), $firewallName);
        }

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        if (in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            return new JsonResponse([], Response::HTTP_FORBIDDEN);
        }

        return new RedirectResponse($this->getLoginUrl($request));
    }
}
