<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\Table(name: 'permission')]
#[ORM\UniqueConstraint(name: 'unique_role_resource', columns: ['role_id', 'resource'])]
#[ApiFilter(SearchFilter::class, properties: ['role' => 'exact', 'resource' => 'exact'])]
#[ApiResource(
    operations: [
        new GetCollection(security: 'is_granted("OIDC_USER")'),
        new Get(security: 'is_granted("OIDC_USER")'),
        new Post(security: 'is_granted("ROLE_SUPER_ADMIN")'),
        new Put(security: 'is_granted("ROLE_SUPER_ADMIN")'),
        new Delete(security: 'is_granted("ROLE_SUPER_ADMIN")'),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Permission:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Permission:write'],
    ],
)]
class Permission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['Permission:read', 'Role:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Role::class, inversedBy: 'permissions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['Permission:read', 'Permission:write'])]
    private ?Role $role = null;

    #[ORM\Column(length: 50)]
    #[Groups(['Permission:read', 'Permission:write', 'Role:read'])]
    private string $resource;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['Permission:read', 'Permission:write', 'Role:read'])]
    private bool $canRead = false;

    #[ORM\Column(options: ['default' => false])]
    #[Groups(['Permission:read', 'Permission:write', 'Role:read'])]
    private bool $canWrite = false;

    public function getId(): ?int { return $this->id; }

    public function getRole(): ?Role { return $this->role; }
    public function setRole(?Role $role): static { $this->role = $role; return $this; }

    public function getResource(): string { return $this->resource; }
    public function setResource(string $resource): static { $this->resource = $resource; return $this; }

    public function getCanRead(): bool { return $this->canRead; }
    public function setCanRead(bool $canRead): static { $this->canRead = $canRead; return $this; }

    public function getCanWrite(): bool { return $this->canWrite; }
    public function setCanWrite(bool $canWrite): static { $this->canWrite = $canWrite; return $this; }
}
