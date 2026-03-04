<?php

namespace App\Domain\Auth\Entity;

use App\Domain\Auth\Enum\TokenRequestType;
use App\Domain\Auth\Repository\TokenRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a single-use token request associated with a user.
 *
 * Tokens are used for sensitive operations such as:
 *  - email verification
 *  - password reset
 *
 * A token request contains:
 *  - a hashed token (never the raw value)
 *  - expiration timestamp
 *  - consumption timestamp
 *
 * A token is considered valid if:
 *  - consumedAt is null
 *  - expiresAt is in the future
 */
#[ORM\Entity(repositoryClass: TokenRequestRepository::class)]
#[ORM\Table(name: 'token_request')]
#[ORM\Index(name: 'idx_token_hash', columns: ['token_hash'])]
#[ORM\Index(name: 'idx_user_type', columns: ['user_id', 'type'])]
class TokenRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $user;

    #[ORM\Column(enumType: TokenRequestType::class)]
    private TokenRequestType $type;

    #[ORM\Column(type: Types::STRING, length: 64, unique: true)]
    private string $tokenHash;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $consumedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): TokenRequestType
    {
        return $this->type;
    }

    public function setType(TokenRequestType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function setTokenHash(string $tokenHash): static
    {
        $this->tokenHash = $tokenHash;

        return $this;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getConsumedAt(): ?\DateTimeImmutable
    {
        return $this->consumedAt;
    }

    public function setConsumedAt(?\DateTimeImmutable $consumedAt): static
    {
        $this->consumedAt = $consumedAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isExpired(?\DateTimeImmutable $now = null): bool
    {
        $now ??= new \DateTimeImmutable();

        return $this->getExpiresAt() <= $now;
    }

    public function isConsumed(): bool
    {
        return null !== $this->getConsumedAt();
    }

    public function canBeUsed(?\DateTimeImmutable $now = null): bool
    {
        return !$this->isConsumed($now) && !$this->isExpired($now);
    }

    public function consume(\DateTimeImmutable $now): void
    {
        if ($this->isConsumed()) {
            return;
        }

        $this->setConsumedAt($now);
    }
}
