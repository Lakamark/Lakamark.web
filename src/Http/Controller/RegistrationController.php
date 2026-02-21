<?php

namespace App\Http\Controller;

use App\Domain\Auth\Authenticator;
use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\BeforeUserRegisterEvent;
use App\Domain\Auth\Event\UserRegisteredEvent;
use App\Foundation\Security\TokenIssuer;
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
    /**
     * @throws RandomException
     */
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em,
        TokenIssuer $tokenIssuer,
        EventDispatcherInterface $dispatcher,
        UserAuthenticatorInterface $userAuthenticator,
        Authenticator $authenticator,
    ): Response {
        // The current user is already logging we will redirect to the homepage
        $alreadyLoggedIn = $this->getUser();
        if ($alreadyLoggedIn) {
            return $this->redirectToRoute('app_login');
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
                ->setCreatedAt(new \DateTimeImmutable())
                ->setConfirmationToken($tokenIssuer->issue()->token);

            // Dispatch BeforeCreatedEvent
            $dispatcher->dispatch(new BeforeUserRegisterEvent($user, $request));

            $em->persist($user);
            $em->flush();

            // Dispatch an UserCreatedEvent.
            // A subscriber (AuthSubscriber)to listen this event to send an email
            $dispatcher->dispatch(new UserRegisteredEvent($user, $isOwner));

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

    #[Route('/register/confirmation/{id}', name: 'app_register_confirm', requirements: ['id' => '\d+'])]
    public function confirm(
        User $user,
        Request $request,
        EntityManagerInterface $em,
    ): RedirectResponse {
        $token = $request->request->get('token');

        // If the token is empty
        // Or does not match with the current user confirmation token in the database
        if (empty($token) || $user->getConfirmationToken() !== $token) {
            $this->addFlash('error', 'Invalid confirmation token.');

            return $this->redirectToRoute('app_register');
        }

        // If the confirmation token is too old
        if ($user->getCreatedAt() < new \DateTimeImmutable('-2 hours')) {
            $this->addFlash('error', 'Your account has expired.');

            return $this->redirectToRoute('app_register');
        }

        // We delete the token confirmation and to set validatedAt datetime.
        // Later, we will use a cron task to delete all unconfirmed accounts
        // or bots account in the database.
        $user
            ->setConfirmationToken(null)
            ->setConfirmAt(new \DateTimeImmutable('now'));

        $em->flush();
        $this->addFlash('success', 'Your account has been confirmed');

        return $this->redirectToRoute('app_login');
    }
}
