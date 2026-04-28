<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use App\Repository\TrainingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity(repositoryClass: TrainingRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiFilter(SearchFilter::class, properties: ['statut' => 'exact', 'eleve' => 'exact', 'instructeur' => 'exact'])]
#[ApiResource(
    uriTemplate: '/trainings{._format}',
    operations: [
        new GetCollection(
            itemUriTemplate: '/trainings/{id}{._format}',
            paginationClientItemsPerPage: true,
        ),
        new Post(
            itemUriTemplate: '/trainings/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Get(
            uriTemplate: '/trainings/{id}{._format}'
        ),
        new Put(
            uriTemplate: '/trainings/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Delete(
            uriTemplate: '/trainings/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Training:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Training:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("OIDC_USER")',
    mercure: true
)]
class Training implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['Training:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['Training:read', 'Training:write'])]
    private ?User $eleve = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(groups: ['Training:read', 'Training:write'])]
    private ?User $instructeur = null;

    #[ORM\ManyToOne(targetEntity: Programme::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['Training:read', 'Training:write'])]
    private ?Programme $programme = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Training:read', 'Training:write'])]
    private ?\DateTimeImmutable $dateDebut = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Training:read', 'Training:write'])]
    private ?\DateTimeImmutable $dateFin = null;

    #[ORM\Column(length: 20, options: ['default' => 'en_cours'])]
    #[Groups(groups: ['Training:read', 'Training:write'])]
    private string $statut = 'en_cours';

    /** @var Collection<int, Progress> */
    #[ORM\OneToMany(targetEntity: Progress::class, mappedBy: 'training', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(groups: ['Training:read'])]
    private Collection $progresses;

    /** @var Collection<int, MediaObject> */
    #[ORM\OneToMany(targetEntity: MediaObject::class, mappedBy: 'training', cascade: ['persist'])]
    #[Groups(groups: ['Training:read', 'Training:write'])]
    private Collection $documents;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Training:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Training:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->progresses = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        if ($this->dateDebut === null) {
            $this->dateDebut = new \DateTimeImmutable();
        }
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

    public function getEleve(): ?User
    {
        return $this->eleve;
    }

    public function setEleve(?User $eleve): static
    {
        $this->eleve = $eleve;
        return $this;
    }

    public function getInstructeur(): ?User
    {
        return $this->instructeur;
    }

    public function setInstructeur(?User $instructeur): static
    {
        $this->instructeur = $instructeur;
        return $this;
    }

    public function getProgramme(): ?Programme
    {
        return $this->programme;
    }

    public function setProgramme(?Programme $programme): static
    {
        $this->programme = $programme;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeImmutable $dateDebut): static
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeImmutable $dateFin): static
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;
        return $this;
    }

    /** @return Collection<int, Progress> */
    public function getProgresses(): Collection
    {
        return $this->progresses;
    }

    public function addProgress(Progress $progress): static
    {
        if (!$this->progresses->contains($progress)) {
            $this->progresses->add($progress);
            $progress->setTraining($this);
        }
        return $this;
    }

    public function removeProgress(Progress $progress): static
    {
        if ($this->progresses->removeElement($progress)) {
            if ($progress->getTraining() === $this) {
                $progress->setTraining(null);
            }
        }
        return $this;
    }

    #[Groups(groups: ['Training:read'])]
    public function getProgressionPercent(): float
    {
        if ($this->progresses->isEmpty()) {
            return 0.0;
        }
        $total = 0;
        foreach ($this->progresses as $p) {
            $total += $p->getNiveau();
        }
        $maxTotal = $this->progresses->count() * 3;
        return $maxTotal > 0 ? round(($total / $maxTotal) * 100, 1) : 0.0;
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
            $document->setTraining($this);
        }
        return $this;
    }

    public function removeDocument(MediaObject $document): static
    {
        if ($this->documents->removeElement($document)) {
            if ($document->getTraining() === $this) {
                $document->setTraining(null);
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
