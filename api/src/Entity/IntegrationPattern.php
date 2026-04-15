<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\Table(name: 'integration_pattern')]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(security: 'is_granted("OIDC_ADMIN")'),
        new Get(security: 'is_granted("OIDC_ADMIN")'),
        new Post(security: 'is_granted("ROLE_SUPER_ADMIN")'),
        new Put(security: 'is_granted("ROLE_SUPER_ADMIN")'),
        new Patch(security: 'is_granted("ROLE_SUPER_ADMIN")'),
        new Delete(security: 'is_granted("ROLE_SUPER_ADMIN")'),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['IntegrationPattern:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['IntegrationPattern:write'],
    ],
    mercure: true
)]
class IntegrationPattern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['IntegrationPattern:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $name = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $code = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $capability = null;

    /** Module client requis pour cette capability (ex: hasMicrotrakTag, hasAI). Auto-assign à l'activation. */
    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $requiredModule = null;

    #[ORM\Column(length: 10)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private string $method = 'GET';

    #[ORM\Column(length: 500)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $urlTemplate = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?array $headers = [];

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?array $queryParams = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $bodyTemplate = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $contentType = 'application/json';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $description = null;

    #[ORM\Column]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private bool $active = true;

    /** Durée de cache en secondes (0 ou null = pas de cache) */
    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?int $cacheTtl = null;

    /** URL de fallback si l'URL principale échoue */
    #[ORM\Column(length: 500, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $fallbackUrlTemplate = null;

    /** @var Collection<int, IntegrationVariable> */
    #[ORM\OneToMany(targetEntity: IntegrationVariable::class, mappedBy: 'pattern', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private Collection $variables;

    /** @var Collection<int, IntegrationResponseMapping> */
    #[ORM\OneToMany(targetEntity: IntegrationResponseMapping::class, mappedBy: 'pattern', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private Collection $responseMappings;

    /** @var Collection<int, Client> */
    #[ORM\ManyToMany(targetEntity: Client::class, inversedBy: 'integrationPatterns')]
    #[ORM\JoinTable(name: 'integration_pattern_client')]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private Collection $clients;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['IntegrationPattern:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    #[Groups(['IntegrationPattern:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->variables = new ArrayCollection();
        $this->responseMappings = new ArrayCollection();
        $this->clients = new ArrayCollection();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getName(): ?string { return $this->name; }
    public function setName(?string $name): static { $this->name = $name; return $this; }

    public function getCode(): ?string { return $this->code; }
    public function setCode(?string $code): static { $this->code = $code; return $this; }

    public function getCapability(): ?string { return $this->capability; }
    public function setCapability(?string $capability): static { $this->capability = $capability; return $this; }

    public function getRequiredModule(): ?string { return $this->requiredModule; }
    public function setRequiredModule(?string $requiredModule): static { $this->requiredModule = $requiredModule; return $this; }

    public function getMethod(): string { return $this->method; }
    public function setMethod(string $method): static { $this->method = strtoupper($method); return $this; }

    public function getUrlTemplate(): ?string { return $this->urlTemplate; }
    public function setUrlTemplate(?string $urlTemplate): static { $this->urlTemplate = $urlTemplate; return $this; }

    public function getHeaders(): ?array { return $this->headers; }
    public function setHeaders(?array $headers): static { $this->headers = $headers; return $this; }

    public function getQueryParams(): ?array { return $this->queryParams; }
    public function setQueryParams(?array $queryParams): static { $this->queryParams = $queryParams; return $this; }

    public function getBodyTemplate(): ?string { return $this->bodyTemplate; }
    public function setBodyTemplate(?string $bodyTemplate): static { $this->bodyTemplate = $bodyTemplate; return $this; }

    public function getContentType(): ?string { return $this->contentType; }
    public function setContentType(?string $contentType): static { $this->contentType = $contentType; return $this; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): static { $this->description = $description; return $this; }

    public function isActive(): bool { return $this->active; }
    public function setActive(bool $active): static { $this->active = $active; return $this; }

    /** @return Collection<int, IntegrationVariable> */
    public function getVariables(): Collection { return $this->variables; }
    public function addVariable(IntegrationVariable $variable): static
    {
        if (!$this->variables->contains($variable)) {
            $this->variables->add($variable);
            $variable->setPattern($this);
        }
        return $this;
    }
    public function removeVariable(IntegrationVariable $variable): static
    {
        if ($this->variables->removeElement($variable) && $variable->getPattern() === $this) {
            $variable->setPattern(null);
        }
        return $this;
    }

    /** @return Collection<int, IntegrationResponseMapping> */
    public function getResponseMappings(): Collection { return $this->responseMappings; }
    public function addResponseMapping(IntegrationResponseMapping $mapping): static
    {
        if (!$this->responseMappings->contains($mapping)) {
            $this->responseMappings->add($mapping);
            $mapping->setPattern($this);
        }
        return $this;
    }
    public function removeResponseMapping(IntegrationResponseMapping $mapping): static
    {
        if ($this->responseMappings->removeElement($mapping) && $mapping->getPattern() === $this) {
            $mapping->setPattern(null);
        }
        return $this;
    }

    /** @return Collection<int, Client> */
    public function getClients(): Collection { return $this->clients; }
    public function addClient(Client $client): static
    {
        if (!$this->clients->contains($client)) {
            $this->clients->add($client);
        }
        return $this;
    }
    public function removeClient(Client $client): static
    {
        $this->clients->removeElement($client);
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): ?\DateTimeImmutable { return $this->updatedAt; }

    public function getCacheTtl(): ?int { return $this->cacheTtl; }
    public function setCacheTtl(?int $cacheTtl): static { $this->cacheTtl = $cacheTtl; return $this; }

    public function getFallbackUrlTemplate(): ?string { return $this->fallbackUrlTemplate; }
    public function setFallbackUrlTemplate(?string $fallbackUrlTemplate): static { $this->fallbackUrlTemplate = $fallbackUrlTemplate; return $this; }
}
