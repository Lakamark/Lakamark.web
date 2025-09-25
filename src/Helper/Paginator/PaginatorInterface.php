<?php

namespace App\Helper\Paginator;

use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * Interface to make a link with the library KnpPaginator.
 */
interface PaginatorInterface
{
    public function allowFilterShort(string ...$fields): self;

    public function paginate(Query $query): PaginationInterface;
}
