<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(paginationClientItemsPerPage: true),
        new Post(security: 'is_granted("OIDC_USER")'),
        new Get(),
        new Put(security: 'is_granted("OIDC_ADMIN")'),
        new Patch(security: 'is_granted("OIDC_ADMIN")'),
        new Delete(security: 'is_granted("OIDC_ADMIN")'),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['SecurityEvent:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['SecurityEvent:write'],
    ],
    order: ['createdAt' => 'DESC'],
    security: 'is_granted("OIDC_USER")',
    mercure: true,
)]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'dateEvenement'])]
#[ApiFilter(SearchFilter::class, properties: ['status' => 'exact', 'type' => 'exact'])]
class SecurityEvent implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['SecurityEvent:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?\DateTimeInterface $dateEvenement = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?User $pilote = null;

    #[ORM\ManyToOne(targetEntity: Aeronef::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?Aeronef $aeronef = null;

    #[ORM\ManyToOne(targetEntity: Prestation::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?Prestation $prestation = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?\DateTimeInterface $dateNotificationExploitant = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?\DateTimeInterface $dateNotificationDGAC = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?\DateTimeInterface $dateNotificationBEA = null;

    #[ORM\Column(length: 20, options: ['default' => 'ouvert'])]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private string $status = 'ouvert';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?string $compteRenduSuivi = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['SecurityEvent:read', 'SecurityEvent:write'])]
    private ?\DateTimeInterface $dateCloture = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['SecurityEvent:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['SecurityEvent:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

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
        $this->syncDateCloture();
    }

    private function syncDateCloture(): void
    {
        if ($this->status === 'clos' && $this->dateCloture === null) {
            $this->dateCloture = new \DateTime();
        } elseif ($this->status !== 'clos' && $this->dateCloture !== null) {
            $this->dateCloture = null;
        }
    }

    public function getId(): ?int { return $this->id; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): static { $this->type = $type; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function getDateEvenement(): ?\DateTimeInterface { return $this->dateEvenement; }
    public function setDateEvenement(?\DateTimeInterface $dateEvenement): static { $this->dateEvenement = $dateEvenement; return $this; }

    public function getPilote(): ?User { return $this->pilote; }
    public function setPilote(?User $pilote): static { $this->pilote = $pilote; return $this; }

    public function getAeronef(): ?Aeronef { return $this->aeronef; }
    public function setAeronef(?Aeronef $aeronef): static { $this->aeronef = $aeronef; return $this; }

    public function getPrestation(): ?Prestation { return $this->prestation; }
    public function setPrestation(?Prestation $prestation): static { $this->prestation = $prestation; return $this; }

    public function getDateNotificationExploitant(): ?\DateTimeInterface { return $this->dateNotificationExploitant; }
    public function setDateNotificationExploitant(?\DateTimeInterface $d): static { $this->dateNotificationExploitant = $d; return $this; }

    public function getDateNotificationDGAC(): ?\DateTimeInterface { return $this->dateNotificationDGAC; }
    public function setDateNotificationDGAC(?\DateTimeInterface $d): static { $this->dateNotificationDGAC = $d; return $this; }

    public function getDateNotificationBEA(): ?\DateTimeInterface { return $this->dateNotificationBEA; }
    public function setDateNotificationBEA(?\DateTimeInterface $d): static { $this->dateNotificationBEA = $d; return $this; }

    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): static { $this->status = $status; return $this; }

    public function getCompteRenduSuivi(): ?string { return $this->compteRenduSuivi; }
    public function setCompteRenduSuivi(?string $compteRenduSuivi): static { $this->compteRenduSuivi = $compteRenduSuivi; return $this; }

    public function getDateCloture(): ?\DateTimeInterface { return $this->dateCloture; }
    public function setDateCloture(?\DateTimeInterface $dateCloture): static { $this->dateCloture = $dateCloture; return $this; }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }
}
