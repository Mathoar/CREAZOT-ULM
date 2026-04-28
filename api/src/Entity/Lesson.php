<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(BooleanFilter::class, properties: ['isAvailable'])]
#[ApiFilter(SearchFilter::class, properties: ['type' => 'exact', 'categorie' => 'ipartial'])]
#[ApiResource(
    uriTemplate: '/lessons{._format}',
    operations: [
        new GetCollection(
            itemUriTemplate: '/lessons/{id}{._format}',
            paginationClientItemsPerPage: true,
            paginationClientEnabled: true,
        ),
        new Post(
            itemUriTemplate: '/lessons/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Get(
            uriTemplate: '/lessons/{id}{._format}'
        ),
        new Put(
            uriTemplate: '/lessons/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Delete(
            uriTemplate: '/lessons/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Lesson:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Lesson:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("OIDC_USER")',
    mercure: true
)]
class Lesson implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['Lesson:read', 'Programme:read', 'Training:read', 'Progress:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['Lesson:read', 'Lesson:write', 'Programme:read', 'Training:read', 'Progress:read'])]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['Lesson:read', 'Lesson:write'])]
    private ?string $briefing = null;

    #[ORM\Column(length: 20, options: ['default' => 'pratique'])]
    #[Groups(groups: ['Lesson:read', 'Lesson:write', 'Programme:read', 'Training:read', 'Progress:read'])]
    private string $type = 'pratique';

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(groups: ['Lesson:read', 'Lesson:write', 'Programme:read', 'Training:read', 'Progress:read'])]
    private ?string $categorie = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(groups: ['Lesson:read', 'Lesson:write'])]
    private bool $isAvailable = true;

    /** @var Collection<int, MediaObject> */
    #[ORM\OneToMany(targetEntity: MediaObject::class, mappedBy: 'lesson', cascade: ['persist'])]
    #[Groups(groups: ['Lesson:read', 'Lesson:write'])]
    private Collection $documents;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Lesson:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Lesson:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->documents = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    #[Groups(groups: ['Lesson:read', 'Programme:read', 'Training:read', 'Progress:read'])]
    public function getName(): ?string
    {
        return $this->nom;
    }

    public function getBriefing(): ?string
    {
        return $this->briefing;
    }

    public function setBriefing(?string $briefing): static
    {
        $this->briefing = $briefing;
        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getCategorie(): ?string
    {
        return $this->categorie;
    }

    public function setCategorie(?string $categorie): static
    {
        $this->categorie = $categorie;
        return $this;
    }

    public function isIsAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    /** @return Collection<int, MediaObject> */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(MediaObject $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->setLesson($this);
        }
        return $this;
    }

    public function removeDocument(MediaObject $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getLesson() === $this) {
                $document->setLesson(null);
            }
        }
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
