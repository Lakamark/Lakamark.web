<?php

namespace App\Domain\Badge\Entity;

use App\Domain\Badge\Repository\BadgeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BadgeRepository::class)]
class Badge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $action_name = null;

    #[ORM\Column(type: Types::INTEGER, options: ['default' => 0])]
    private ?int $action_count = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private ?\DateTimeImmutable $updatedAt;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => 0])]
    private ?bool $unlockable = false;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getActionName(): ?string
    {
        return $this->action_name;
    }

    public function setActionName(string $action_name): static
    {
        $this->action_name = $action_name;

        return $this;
    }

    public function getActionCount(): int
    {
        return $this->action_count;
    }

    public function setActionCount(int $action_count): static
    {
        $this->action_count = $action_count;

        return $this;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isUnlockable(): bool
    {
        return $this->unlockable;
    }

    public function setUnlockable(bool $unlockable): static
    {
        $this->unlockable = $unlockable;

        return $this;
    }
}
