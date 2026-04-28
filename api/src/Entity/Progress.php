<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\ProgressRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity(repositoryClass: ProgressRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\UniqueConstraint(name: 'unique_training_lesson', columns: ['training_id', 'lesson_id'])]
#[ApiFilter(SearchFilter::class, properties: ['training' => 'exact'])]
#[ApiResource(
    uriTemplate: '/progresses{._format}',
    operations: [
        new GetCollection(
            itemUriTemplate: '/progresses/{id}{._format}',
            paginationClientItemsPerPage: true,
        ),
        new Get(
            uriTemplate: '/progresses/{id}{._format}'
        ),
        new Put(
            uriTemplate: '/progresses/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Patch(
            uriTemplate: '/progresses/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")',
            inputFormats: ['json' => ['application/merge-patch+json']],
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Progress:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Progress:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("OIDC_USER")',
    mercure: true
)]
class Progress implements TenantAwareInterface
{
    use TenantAwareTrait;

    public const NIVEAU_NON_ABORDE = 0;
    public const NIVEAU_PRESENTE = 1;
    public const NIVEAU_EN_ACQUISITION = 2;
    public const NIVEAU_ACQUIS = 3;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['Progress:read', 'Training:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Training::class, inversedBy: 'progresses')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(groups: ['Progress:read'])]
    private ?Training $training = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(groups: ['Progress:read', 'Training:read'])]
    private ?Lesson $lesson = null;

    #[ORM\Column(type: 'smallint', options: ['default' => 0])]
    #[Groups(groups: ['Progress:read', 'Progress:write', 'Training:read'])]
    private int $niveau = self::NIVEAU_NON_ABORDE;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['Progress:read', 'Progress:write', 'Training:read'])]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['Progress:read'])]
    private ?User $updatedBy = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Progress:read', 'Training:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTraining(): ?Training
    {
        return $this->training;
    }

    public function setTraining(?Training $training): static
    {
        $this->training = $training;
        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getNiveau(): int
    {
        return $this->niveau;
    }

    public function setNiveau(int $niveau): static
    {
        $this->niveau = max(0, min(3, $niveau));
        return $this;
    }

    #[Groups(groups: ['Progress:read', 'Training:read'])]
    public function getNiveauLabel(): string
    {
        return match ($this->niveau) {
            self::NIVEAU_PRESENTE => 'Présenté',
            self::NIVEAU_EN_ACQUISITION => 'En acquisition',
            self::NIVEAU_ACQUIS => 'Acquis',
            default => 'Non abordé',
        };
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;
        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): static
    {
        $this->updatedBy = $updatedBy;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
