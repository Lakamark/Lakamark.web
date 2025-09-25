<?php

namespace App\Http\Controller;

use App\Domain\Blog\Entity\Post;
use App\Domain\Blog\Repository\PostRepository;
use App\Http\Requirements;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly PaginatorInterface $paginator,
    ) {
    }

    #[Route(path: '/blog', name: 'blog.index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $query = $this->postRepository->findAll();
        $posts = $this->paginator->paginate(
            $query,
            $page,
            10
        );

        return $this->render('blog/index.html.twig', ['posts' => $posts]);
    }

    #[Route(path: '/blog/{slug:post}', name: 'blog.show', requirements: ['slug' => Requirements::SLUG])]
    public function show(Post $post): Response
    {
        return $this->render('blog/show.html.twig', ['post' => $post]);
    }
}
