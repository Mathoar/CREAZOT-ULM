<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use App\Repository\ProgrammeRepository;
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

#[ORM\Entity(repositoryClass: ProgrammeRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(BooleanFilter::class, properties: ['isAvailable'])]
#[ApiResource(
    uriTemplate: '/programmes{._format}',
    operations: [
        new GetCollection(
            itemUriTemplate: '/programmes/{id}{._format}',
            paginationClientItemsPerPage: true,
        ),
        new Post(
            itemUriTemplate: '/programmes/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Get(
            uriTemplate: '/programmes/{id}{._format}'
        ),
        new Put(
            uriTemplate: '/programmes/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Delete(
            uriTemplate: '/programmes/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Programme:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Programme:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("OIDC_USER")',
    mercure: true
)]
class Programme implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['Programme:read', 'Training:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['Programme:read', 'Programme:write', 'Training:read'])]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['Programme:read', 'Programme:write'])]
    private ?string $description = null;

    #[ORM\Column(length: 50, options: ['default' => 'brevet'])]
    #[Groups(groups: ['Programme:read', 'Programme:write', 'Training:read'])]
    private string $type = 'brevet';

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(groups: ['Programme:read', 'Programme:write'])]
    private bool $isAvailable = true;

    /** @var Collection<int, ProgrammeLesson> */
    #[ORM\OneToMany(targetEntity: ProgrammeLesson::class, mappedBy: 'programme', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[Groups(groups: ['Programme:read', 'Programme:write'])]
    private Collection $programmeLessons;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Programme:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Programme:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->programmeLessons = new ArrayCollection();
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

    #[Groups(groups: ['Programme:read', 'Training:read'])]
    public function getName(): ?string
    {
        return $this->nom;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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

    public function isIsAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    /** @return Collection<int, ProgrammeLesson> */
    public function getProgrammeLessons(): Collection
    {
        return $this->programmeLessons;
    }

    public function addProgrammeLesson(ProgrammeLesson $programmeLesson): static
    {
        if (!$this->programmeLessons->contains($programmeLesson)) {
            $this->programmeLessons->add($programmeLesson);
            $programmeLesson->setProgramme($this);
        }
        return $this;
    }

    public function removeProgrammeLesson(ProgrammeLesson $programmeLesson): static
    {
        if ($this->programmeLessons->removeElement($programmeLesson)) {
            if ($programmeLesson->getProgramme() === $this) {
                $programmeLesson->setProgramme(null);
            }
        }
        return $this;
    }

    #[Groups(groups: ['Programme:read'])]
    public function getLessonCount(): int
    {
        return $this->programmeLessons->count();
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
