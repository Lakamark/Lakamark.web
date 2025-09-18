<?php

namespace App\Http\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    #[Route(path: '/blog', name: 'blog.index')]
    public function index(): Response
    {
        return $this->render('blog/index.html.twig');
    }
}