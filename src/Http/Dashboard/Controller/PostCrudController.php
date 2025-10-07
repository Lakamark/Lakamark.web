<?php

namespace App\Http\Dashboard\Controller;

use App\Domain\Blog\Entity\Post;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PostCrudController extends CrudController
{
    protected string $entity = Post::class;
    protected string $routePrefix = 'posts.index';
    protected string $viewPath = 'posts';
    protected bool $indexedOnSave = false;
    protected array $crudEvents = [];
    protected array $crudFlashesMessages = [
        'onSuccess' => 'Post created.',
        'onFailure' => 'Post deleted.',
    ];

    #[Route(path: '/posts', name: 'posts.index')]
    public function index(): Response
    {
        return $this->actionCrudIndex();
    }
}
