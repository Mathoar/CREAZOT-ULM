<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use App\Repository\OptionRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ApiFilter(BooleanFilter::class, properties: ['isAvailable'])]
#[ApiResource(
    uriTemplate: '/options{._format}',
    operations: [
        new GetCollection(
            itemUriTemplate: '/options/{id}{._format}',
            paginationClientItemsPerPage: true,
        ),
        new Post(
            itemUriTemplate: '/options/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Get(
            uriTemplate: '/options/{id}{._format}'
        ),
        new Put(
            uriTemplate: '/options/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Delete(
            uriTemplate: '/options/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Option:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Option:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("OIDC_USER")',
    mercure: true
)]
class Option implements TenantAwareInterface
{
    use TenantAwareTrait;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['Option:write', 'Option:read', 'Vol:read', 'Cadeau:read', 'Prestation:read', 'Reservation:read', 'Combinaison:read', 'PaymentDetail:read', 'Payment:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 60, nullable: true)]
    #[Groups(groups: ['Option:write', 'Option:read', 'Vol:read', 'Cadeau:read', 'Prestation:read', 'Reservation:read', 'Combinaison:read', 'PaymentDetail:read', 'Payment:read'])]
    private ?string $nom = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Option:write', 'Option:read', 'Vol:read', 'Cadeau:read', 'Prestation:read', 'Reservation:read', 'Combinaison:read', 'PaymentDetail:read', 'Payment:read'])]
    private ?float $prix = null;

    #[ORM\Column(nullable: true)]
    #[Groups(groups: ['Option:write', 'Option:read', 'Vol:read', 'Cadeau:read', 'Prestation:read', 'Reservation:read', 'Combinaison:read', 'PaymentDetail:read', 'Payment:read'])]
    private ?float $tauxTva = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    #[Groups(groups: ['Option:write', 'Option:read'])]
    private bool $isAvailable = true;

    #[Groups(groups: ['Option:write', 'Option:read', 'Vol:read', 'Cadeau:read', 'Prestation:read', 'Reservation:read', 'Combinaison:read', 'PaymentDetail:read', 'Payment:read'])]
    public function getName(): ?string
    {
        return $this->nom;
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

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): static
    {
        $this->prix = $prix;

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

    public function getTauxTva(): ?float
    {
        return $this->tauxTva;
    }

    public function setTauxTva(?float $tauxTva): static
    {
        $this->tauxTva = $tauxTva;
        return $this;
    }

    #[Groups(groups: ['Option:read'])]
    public function getPrixHT(): ?float
    {
        if ($this->prix === null) {
            return null;
        }
        $tva = $this->tauxTva ?? 0.0;
        return $tva > 0 ? round($this->prix / (1 + $tva), 2) : $this->prix;
    }
}
