<?php

namespace App\Domain\Application\Entity;

use App\Domain\Application\ContentWorkflowTrait;
use App\Domain\Application\Contract\ReadableContentInterface;
use App\Domain\Application\Enum\AccessLevel;
use App\Domain\Application\Enum\ContentStatus;
use App\Domain\Application\Exception\ContentLogicException;
use App\Domain\Application\Exception\DoubleSetException;
use App\Domain\Application\Repository\ContentRepository;
use App\Domain\Auth\Entity\User;
use App\Domain\Blog\Entity\Post;
use App\Domain\Project\Entity\Project;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ContentRepository::class)]
#[ORM\Table(
    name: 'content',
    indexes: [
        new ORM\Index(name: 'idx_content_status', columns: ['status']),
        new ORM\Index(name: 'idx_content_access_level', columns: ['access_level']),
        new ORM\Index(name: 'idx_content_created_at', columns: ['created_at']),
        new ORM\Index(name: 'idx_content_feed', columns: ['status', 'access_level', 'created_at']),
        new ORM\Index(name: 'idx_content_kind_created', columns: ['kind', 'created_at']),
    ],
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_content_slug', columns: ['slug']),
    ],
)]
#[ORM\HasLifecycleCallbacks]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'kind', type: Types::STRING, length: 32)]
#[ORM\DiscriminatorMap([
    'post' => Post::class,
    'project' => Project::class,
])]
abstract class Content implements ReadableContentInterface
{
    use ContentWorkflowTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $title;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $excerpt = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $content = '';

    #[ORM\Column(enumType: ContentStatus::class)]
    private ContentStatus $status = ContentStatus::DRAFT;

    #[ORM\Column(enumType: AccessLevel::class)]
    private AccessLevel $accessLevel = AccessLevel::PUBLIC;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(name: 'published_at', type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'contents')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private User $author;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $archivedAt = null;

    #[ORM\PrePersist]
    public function assertCreatedAtIsSet(): void
    {
        // To ensure to define the createdAt
        if (null === $this->createdAt) {
            $this->setCreatedAt(new \DateTimeImmutable());
        }
    }

    #[ORM\PreUpdate]
    public function touchUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getExcerpt(): ?string
    {
        return $this->excerpt;
    }

    public function setExcerpt(?string $excerpt): static
    {
        $this->excerpt = $excerpt;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): ContentStatus
    {
        return $this->status;
    }

    public function setStatus(ContentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getAccessLevel(): AccessLevel
    {
        return $this->accessLevel;
    }

    public function setAccessLevel(AccessLevel $accessLevel): static
    {
        $this->accessLevel = $accessLevel;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * createdAt is immutable and can only be set once.
     *
     * @throws DoubleSetException
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        if (null !== $this->createdAt) {
            throw DoubleSetException::for('createdAt');
        }
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function setArchivedAt(?\DateTimeImmutable $archivedAt): static
    {
        $this->archivedAt = $archivedAt;

        return $this;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function isDraft(): bool
    {
        return ContentStatus::DRAFT === $this->status;
    }

    public function isPublished(): bool
    {
        return ContentStatus::PUBLISHED === $this->status;
    }

    public function isArchived(): bool
    {
        return ContentStatus::ARCHIVED === $this->status;
    }

    private function assertHasSlug(): void
    {
        if (null === $this->slug || '' === trim($this->slug)) {
            throw new ContentLogicException('Content.slug must be set before publishing.');
        }
    }
}
