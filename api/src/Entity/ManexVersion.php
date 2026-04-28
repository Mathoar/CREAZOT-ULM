<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\Table(name: 'manex_version')]
#[ApiFilter(SearchFilter::class, properties: ['client' => 'exact'])]
#[ApiResource(
    uriTemplate: '/manex_versions{._format}',
    operations: [
        new GetCollection(
            itemUriTemplate: '/manex_versions/{id}{._format}',
            order: ['generatedAt' => 'DESC'],
        ),
        new Get(uriTemplate: '/manex_versions/{id}{._format}'),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['ManexVersion:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    security: 'is_granted("OIDC_ADMIN")',
    paginationEnabled: false,
)]
class ManexVersion implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['ManexVersion:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Groups(groups: ['ManexVersion:read'])]
    private string $versionNumber;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(groups: ['ManexVersion:read'])]
    private \DateTimeImmutable $generatedAt;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[Groups(groups: ['ManexVersion:read'])]
    private ?User $generatedBy = null;

    #[ORM\OneToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(groups: ['ManexVersion:read'])]
    private ?MediaObject $document = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['ManexVersion:read'])]
    private ?string $changelog = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $sectionSnapshot = null;

    public function __construct()
    {
        $this->generatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getVersionNumber(): string { return $this->versionNumber; }
    public function setVersionNumber(string $v): static { $this->versionNumber = $v; return $this; }

    public function getGeneratedAt(): \DateTimeImmutable { return $this->generatedAt; }
    public function setGeneratedAt(\DateTimeImmutable $v): static { $this->generatedAt = $v; return $this; }

    public function getGeneratedBy(): ?User { return $this->generatedBy; }
    public function setGeneratedBy(?User $v): static { $this->generatedBy = $v; return $this; }

    public function getDocument(): ?MediaObject { return $this->document; }
    public function setDocument(?MediaObject $v): static { $this->document = $v; return $this; }

    public function getChangelog(): ?string { return $this->changelog; }
    public function setChangelog(?string $v): static { $this->changelog = $v; return $this; }

    public function getSectionSnapshot(): ?array { return $this->sectionSnapshot; }
    public function setSectionSnapshot(?array $v): static { $this->sectionSnapshot = $v; return $this; }
}
