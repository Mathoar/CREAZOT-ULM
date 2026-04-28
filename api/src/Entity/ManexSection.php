<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\Table(name: 'manex_section')]
#[ORM\UniqueConstraint(name: 'unique_client_section', columns: ['client_id', 'section_key'])]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: ['client' => 'exact'])]
#[ApiResource(
    uriTemplate: '/manex_sections{._format}',
    operations: [
        new GetCollection(itemUriTemplate: '/manex_sections/{id}{._format}'),
        new Get(uriTemplate: '/manex_sections/{id}{._format}'),
        new Patch(
            uriTemplate: '/manex_sections/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['ManexSection:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['ManexSection:write'],
    ],
    security: 'is_granted("OIDC_ADMIN")',
    paginationEnabled: false,
)]
class ManexSection implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['ManexSection:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Groups(groups: ['ManexSection:read'])]
    private string $sectionKey;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['ManexSection:read', 'ManexSection:write'])]
    private string $title;

    #[ORM\Column]
    #[Groups(groups: ['ManexSection:read', 'ManexSection:write'])]
    private int $position;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(groups: ['ManexSection:read', 'ManexSection:write'])]
    private bool $isEnabled = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['ManexSection:read', 'ManexSection:write'])]
    private ?string $introHtml = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['ManexSection:read', 'ManexSection:write'])]
    private ?string $customHtml = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(groups: ['ManexSection:read'])]
    private bool $hasAutoContent = false;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(groups: ['ManexSection:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getSectionKey(): string { return $this->sectionKey; }
    public function setSectionKey(string $v): static { $this->sectionKey = $v; return $this; }

    public function getTitle(): string { return $this->title; }
    public function setTitle(string $v): static { $this->title = $v; return $this; }

    public function getPosition(): int { return $this->position; }
    public function setPosition(int $v): static { $this->position = $v; return $this; }

    public function getIsEnabled(): bool { return $this->isEnabled; }
    public function setIsEnabled(bool $v): static { $this->isEnabled = $v; return $this; }

    public function getIntroHtml(): ?string { return $this->introHtml; }
    public function setIntroHtml(?string $v): static { $this->introHtml = $v; return $this; }

    public function getCustomHtml(): ?string { return $this->customHtml; }
    public function setCustomHtml(?string $v): static { $this->customHtml = $v; return $this; }

    public function getHasAutoContent(): bool { return $this->hasAutoContent; }
    public function setHasAutoContent(bool $v): static { $this->hasAutoContent = $v; return $this; }

    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
