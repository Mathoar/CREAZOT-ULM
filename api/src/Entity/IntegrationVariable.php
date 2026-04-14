<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'integration_variable')]
class IntegrationVariable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['IntegrationPattern:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: IntegrationPattern::class, inversedBy: 'variables')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?IntegrationPattern $pattern = null;

    #[ORM\Column(length: 50)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $variableName = null;

    #[ORM\Column(length: 20)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $source = null;

    #[ORM\Column(length: 100, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $sourceField = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private ?string $defaultValue = null;

    #[ORM\Column]
    #[Groups(['IntegrationPattern:read', 'IntegrationPattern:write'])]
    private bool $required = true;

    public function getId(): ?int { return $this->id; }

    public function getPattern(): ?IntegrationPattern { return $this->pattern; }
    public function setPattern(?IntegrationPattern $pattern): static { $this->pattern = $pattern; return $this; }

    public function getVariableName(): ?string { return $this->variableName; }
    public function setVariableName(?string $variableName): static { $this->variableName = $variableName; return $this; }

    public function getSource(): ?string { return $this->source; }
    public function setSource(?string $source): static { $this->source = $source; return $this; }

    public function getSourceField(): ?string { return $this->sourceField; }
    public function setSourceField(?string $sourceField): static { $this->sourceField = $sourceField; return $this; }

    public function getDefaultValue(): ?string { return $this->defaultValue; }
    public function setDefaultValue(?string $defaultValue): static { $this->defaultValue = $defaultValue; return $this; }

    public function isRequired(): bool { return $this->required; }
    public function setRequired(bool $required): static { $this->required = $required; return $this; }
}
