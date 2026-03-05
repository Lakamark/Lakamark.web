<?php

namespace App\Http\Controller;

use App\Domain\Auth\Authenticator;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Domain\Auth\Exception\InvalidTokenException;
use App\Domain\Auth\Service\TokenRequestService;
use App\Domain\Auth\Service\UserRoleManagerService;
use App\Http\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Random\RandomException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private readonly UserRoleManagerService $userRoleManagerService,
    ) {
    }

    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em,
        TokenRequestService $tokenRequestService,
        EventDispatcherInterface $dispatcher,
        UserAuthenticatorInterface $userAuthenticator,
        Authenticator $authenticator,
    ): Response {
        // The current user is already logging we will redirect to the homepage
        $alreadyLoggedIn = $this->getUser();
        if ($alreadyLoggedIn) {
            return $this->redirectToRoute('app_account');
        }

        $user = new User();
        $isOwner = (bool) $request->request->get('oauth');
        $env = $this->getParameter('kernel.environment');
        $rootErrors = [];

        // We disabled the captcha for testing purpose.
        $form = $this->createForm(RegistrationFormType::class, $user, [
            'with_captcha_puzzle' => 'test' !== $env,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Save the new User Entity
            $user
                ->setPassword($userPasswordHasher->hashPassword($user, $plainPassword))
                ->setCreatedAt(new \DateTimeImmutable());

            // Dispatch BeforeCreatedEvent
            $dispatcher->dispatch(new BeforeUserRegisterEvent($user, $request));

            $em->persist($user);
            $em->flush();

            // prepare the token request
            $issued = $tokenRequestService->issue(
                user: $user,
                type: TokenRequestType::REGISTER_CONFIRMATION,
                now: new \DateTimeImmutable(),
            );

            // Dispatch an UserCreatedEvent.
            // A subscriber (AuthSubscriber)to listen this event to send an email
            $dispatcher->dispatch(new UserRegisteredEvent($issued, $isOwner));

            if ($isOwner) {
                $this->addFlash('success', 'Almost there, you should to confirm your account.');

                return $userAuthenticator->authenticateUser($user, $authenticator, $request);
            }

            return $this->redirectToRoute('app_login');
        } elseif ($form->isSubmitted()) {
            /** @var FormError $error */
            foreach ($form->getErrors() as $error) {
                if (null === $error->getCause()) {
                    $rootErrors[] = $error;
                }
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            'errors' => $rootErrors,
        ]);
    }

    #[Route('/register/confirmation', name: 'app_register_confirm', methods: ['GET'])]
    public function confirm(
        Request $request,
        TokenRequestService $tokenRequestService,
        EntityManagerInterface $em,
    ): RedirectResponse {
        $hash = (string) $request->query->get('token', '');

        // Empty token protection
        if ('' === trim($hash)) {
            $this->addFlash('error', 'Invalid confirmation token.');

            return $this->redirectToRoute('app_register');
        }

        try {
            $tokenRequest = $tokenRequestService->consume(
                hash: $hash,
                type: TokenRequestType::REGISTER_CONFIRMATION,
                now: new \DateTimeImmutable(),
            );
        } catch (InvalidTokenException) {
            $this->addFlash('error', 'Invalid or expired confirmation token.');

            return $this->redirectToRoute('app_register');
        }

        // Get the user from the TokenRequest relation
        $user = $tokenRequest->getUser();

        // If already confirmed we don't do anything
        if (null === $user->getConfirmAt()) {
            $user->setConfirmAt(new \DateTimeImmutable());

            $this->userRoleManagerService->markVerified($user);

            $em->flush();
        }

        $this->addFlash('success', 'You have successfully confirmed your account.');

        return $this->redirectToRoute('app_login');
    }
}
