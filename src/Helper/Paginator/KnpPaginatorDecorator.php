<?php

namespace App\Helper\Paginator;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Decorated the KnpPaginator clas to adapt to ours logic domain.
 */
class KnpPaginatorDecorator implements PaginatorInterface
{
    private array $allowedShortedFields = [];

    public function __construct(
        private readonly \Knp\Component\Pager\PaginatorInterface $paginator,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function allowFilterShort(string ...$fields): PaginatorInterface
    {
        $this->allowedShortedFields = array_merge($this->allowedShortedFields, $fields);

        return $this;
    }

    public function paginate(Query $query): PaginationInterface
    {
        $request = $this->requestStack->getCurrentRequest();
        $page = $request->query->get('page', 1);

        // If the index page is negative value (-1) we trow an exception
        if ($page <= 0) {
            throw new PageOutOfBoundException();
        }

        return $this->paginator->paginate(
            $query,
            $page,
            $query->getMaxResults() ?: 10, [
                'sortAllowFieldList' => $this->allowedShortedFields,
                'filterAllowFieldList' => [],
            ]);
    }
}
