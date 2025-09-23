<?php

namespace App\Http\Controller;

use App\Domain\Blog\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->find5posts();

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
}
