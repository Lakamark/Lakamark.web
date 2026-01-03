<?php

namespace App\Domain\Blog\Entity;

use App\Domain\Application\Entity\Content;
use App\Domain\Blog\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post extends Content
{
}
