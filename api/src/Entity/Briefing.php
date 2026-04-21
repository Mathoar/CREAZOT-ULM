<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Put;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\Table(name: 'briefing')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    uriTemplate: '/briefings{._format}',
    operations: [
        new GetCollection(itemUriTemplate: '/briefings/{id}{._format}'),
        new Get(uriTemplate: '/briefings/{id}{._format}'),
        new Put(
            uriTemplate: '/briefings/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Patch(
            uriTemplate: '/briefings/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Briefing:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Briefing:write'],
    ],
    security: 'is_granted("OIDC_USER")',
    paginationEnabled: false,
)]
class Briefing implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['Briefing:read', 'Client:read'])]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'briefing', targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'owner_client_id', referencedColumnName: 'id', nullable: false, unique: true)]
    #[Groups(groups: ['Briefing:read'])]
    private ?Client $ownerClient = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['Briefing:read', 'Briefing:write', 'Client:read'])]
    private ?string $html = null;

    #[ORM\ManyToOne(targetEntity: MediaObject::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[Groups(groups: ['Briefing:read', 'Briefing:write'])]
    private ?MediaObject $headerImage = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => true])]
    #[Groups(groups: ['Briefing:read', 'Briefing:write', 'Client:read'])]
    private bool $showMap = true;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(groups: ['Briefing:read', 'Briefing:write', 'Client:read'])]
    private ?string $extraContacts = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(groups: ['Briefing:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnerClient(): ?Client
    {
        return $this->ownerClient;
    }

    public function setOwnerClient(?Client $client): static
    {
        $this->ownerClient = $client;
        return $this;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function setHtml(?string $html): static
    {
        $this->html = $html;
        return $this;
    }

    public function getHeaderImage(): ?MediaObject
    {
        return $this->headerImage;
    }

    public function setHeaderImage(?MediaObject $headerImage): static
    {
        $this->headerImage = $headerImage;
        return $this;
    }

    public function isShowMap(): bool
    {
        return $this->showMap;
    }

    public function setShowMap(bool $showMap): static
    {
        $this->showMap = $showMap;
        return $this;
    }

    public function getExtraContacts(): ?string
    {
        return $this->extraContacts;
    }

    public function setExtraContacts(?string $extraContacts): static
    {
        $this->extraContacts = $extraContacts;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
