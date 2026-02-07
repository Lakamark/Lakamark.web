<?php

namespace App\Domain\Project\Entity;

use App\Domain\Application\Entity\Content;
use App\Domain\Project\Repository\ProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project extends Content
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $clientName = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $clientUrl = null;

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): static
    {
        $this->clientName = null !== $clientName && '' !== trim($clientName)
            ? $clientName
            : null;

        return $this;
    }

    public function getClientUrl(): ?string
    {
        return $this->clientUrl;
    }

    public function setClientUrl(?string $clientUrl): static
    {
        $this->clientUrl = $clientUrl ?: null;

        return $this;
    }

    public function getProjectYear(): ?int
    {
        $publishedAt = $this->getPublishedAt();

        return null !== $publishedAt?->format('Y') ? (int) $publishedAt->format('Y') : null;
    }

    public function isClientProject(): bool
    {
        $name = $this->getClientName();

        return null !== $name && '' !== trim($name);
    }

    public function isPersonalProject(): bool
    {
        return null === $this->getClientName();
    }
}
