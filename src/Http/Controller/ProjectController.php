<?php

namespace App\Http\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProjectController extends AbstractController
{
    #[Route(path: '/projects', name: 'app_projects', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('project/index.html.twig', []);
    }
}
