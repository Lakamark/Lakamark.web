<?php

namespace App\Domain\Moderation\Entity;

use App\Domain\Auth\Entity\User;
use App\Domain\Moderation\Enum\BanReasonEnum;
use App\Domain\Moderation\Repository\UserBanRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserBanRepository::class)]
#[ORM\Table(name: 'user_ban')]
#[ORM\Index(name: 'idx_user_ban_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_user_ban_lookup_active', columns: ['user_id', 'ended_at', 'expires_at'])]
class UserBan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(enumType: BanReasonEnum::class)]
    private BanReasonEnum $banReason;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getBanReason(): BanReasonEnum
    {
        return $this->banReason;
    }

    public function setBanReason(BanReasonEnum $banReason): static
    {
        $this->banReason = $banReason;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(?\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function endManually(\DateTimeImmutable $now): void
    {
        $this->setEndedAt($now);
    }

    public function endByExpiration(): void
    {
        if (null === $this->getExpiresAt()) {
            throw new \LogicException('Permanent ban cannot expire.');
        }

        $this->setEndedAt($this->expiresAt);
    }
}
