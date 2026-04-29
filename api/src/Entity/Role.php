<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\Table(name: 'role')]
#[ORM\UniqueConstraint(name: 'unique_role_code', columns: ['code'])]
#[ApiResource(
    operations: [
        new GetCollection(security: 'is_granted("OIDC_USER")'),
        new Get(security: 'is_granted("OIDC_USER")'),
        new Post(security: 'is_granted("ROLE_SUPER_ADMIN")'),
        new Put(security: 'is_granted("ROLE_SUPER_ADMIN")'),
        new Delete(security: 'is_granted("ROLE_SUPER_ADMIN")'),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['Role:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['Role:write'],
    ],
)]
class Role
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['Role:read', 'Permission:read', 'UserClientRole:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 30, unique: true)]
    #[Groups(['Role:read', 'Role:write', 'Permission:read', 'UserClientRole:read'])]
    private string $code;

    #[ORM\Column(length: 100)]
    #[Groups(['Role:read', 'Role:write', 'Permission:read', 'UserClientRole:read'])]
    private string $label;

    #[ORM\Column(options: ['default' => true])]
    #[Groups(['Role:read'])]
    private bool $isSystem = true;

    /** @var Collection<int, Permission> */
    #[ORM\OneToMany(targetEntity: Permission::class, mappedBy: 'role', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Groups(['Role:read'])]
    private Collection $permissions;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getCode(): string { return $this->code; }
    public function setCode(string $code): static { $this->code = $code; return $this; }

    public function getLabel(): string { return $this->label; }
    public function setLabel(string $label): static { $this->label = $label; return $this; }

    public function getIsSystem(): bool { return $this->isSystem; }
    public function setIsSystem(bool $isSystem): static { $this->isSystem = $isSystem; return $this; }

    /** @return Collection<int, Permission> */
    public function getPermissions(): Collection { return $this->permissions; }

    public function addPermission(Permission $permission): static
    {
        if (!$this->permissions->contains($permission)) {
            $this->permissions->add($permission);
            $permission->setRole($this);
        }
        return $this;
    }

    public function removePermission(Permission $permission): static
    {
        if ($this->permissions->removeElement($permission)) {
            if ($permission->getRole() === $this) {
                $permission->setRole(null);
            }
        }
        return $this;
    }
}
