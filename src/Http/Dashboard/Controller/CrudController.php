<?php

namespace App\Http\Dashboard\Controller;

use App\Domain\Application\Entity\Content;
use App\Helper\Paginator\PaginatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

abstract class CrudController extends BaseController
{
    protected string $entity = Content::class;
    protected string $viewPath = 'posts';
    protected string $menuLabel = '';
    protected string $routePrefix = '';
    protected string $searchField = 'title';
    protected bool $indexedOnSave = true;
    protected array $crudEvents = [
        'onUpdate' => null,
        'onDelete' => null,
        'onCreate' => null,
    ];
    protected array $crudFlashesMessages = [
        'onSuccess' => 'Item has been saved.',
        'onFailure' => 'Item could not be saved.',
    ];

    public function __construct(
        protected EntityManagerInterface $em,
        protected PaginatorInterface $paginator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function actionCrudIndex(?QueryBuilder $query = null, array $customParams = []): Response
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();

        $query = $query ?: $this->getRepository()
            ->createQueryBuilder('row')
            ->orderBy('row.createdAt', 'DESC');

        // If the user make a search
        if ($request->query->get('q')) {
            $query = $this->applySearch(trim((string) $request->query->get('q')), $query);
        }

        $this->paginator->allowFilterShort('row.id', 'row.title');
        $rows = $this->paginator->paginate($query->getQuery());
        

        return $this->render("dashboard/$this->viewPath/index.html.twig", [
            'rows' => $rows,
            'searchable' => true,
            'menuLabel' => $this->menuLabel,
            'routePrefix' => $this->routePrefix,
            ...$customParams,
        ]);
    }

    /**
     * To get the repository easier way from the EntityManagerInterface.
     */
    public function getRepository(): EntityRepository
    {
        /* @var EntityRepository */
        return $this->em->getRepository($this->entity);
    }

    /**
     * To modify the query if the user make a search.
     */
    public function applySearch(string $searchQuery, QueryBuilder $query): QueryBuilder
    {
        return $query
            ->where("LOWER(row.$this->searchField) LIKE :searchQuery")
            ->setParameter('searchQuery', '%'.strtolower($searchQuery).'%');
    }
}
