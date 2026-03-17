<?php

namespace App\Http\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route(path: '/events', name: 'app_events', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('event/index.html.twig', []);
    }
}