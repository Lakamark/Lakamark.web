<?php

namespace App\Http\Controller;

use App\Domain\Auth\Entity\User;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * Display flashes form errors.
     */
    protected function flashErrors(FormInterface $form): void
    {
        /** @var FormError[] $errors */
        $errors = $form->getErrors();
        $messages = [];

        foreach ($errors as $error) {
            $messages[] = $error->getMessage();
        }
        $this->addFlash('danger', implode("\n", $messages));
    }

    /**
     * To get the user current or return an AccessDenied exception.
     */
    protected function getUserOrException(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }

        return $user;
    }
}
