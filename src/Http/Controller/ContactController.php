<?php

namespace App\Http\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route(path: '/contact', name: 'app_contact', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig');
    }
}
