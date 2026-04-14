<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'integration_response_mapping')]
class IntegrationResponseMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['IntegrationPattern:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: IntegrationPattern::class, inversedBy: 'responseMappings')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?IntegrationPattern $pattern = null;

    #[ORM\Column(length: 100)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $internalField = null;

    #[ORM\Column(length: 200)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $externalPath = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $transformer = null;

    public function getId(): ?int { return $this->id; }

    public function getPattern(): ?IntegrationPattern { return $this->pattern; }
    public function setPattern(?IntegrationPattern $pattern): static { $this->pattern = $pattern; return $this; }

    public function getInternalField(): ?string { return $this->internalField; }
    public function setInternalField(?string $internalField): static { $this->internalField = $internalField; return $this; }

    public function getExternalPath(): ?string { return $this->externalPath; }
    public function setExternalPath(?string $externalPath): static { $this->externalPath = $externalPath; return $this; }

    public function getTransformer(): ?string { return $this->transformer; }
    public function setTransformer(?string $transformer): static { $this->transformer = $transformer; return $this; }
}
