<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('OIDC_ADMIN')"),
        new Put(security: "is_granted('OIDC_ADMIN')"),
        new Patch(security: "is_granted('OIDC_ADMIN')"),
        new Delete(security: "is_granted('OIDC_ADMIN')"),
    ],
    normalizationContext: ['groups' => ['MessageTemplate:read']],
    denormalizationContext: ['groups' => ['MessageTemplate:write']],
)]
class MessageTemplate implements TenantAwareInterface
{
    use TenantAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['MessageTemplate:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['MessageTemplate:read', 'MessageTemplate:write'])]
    private ?string $title = null;

    #[ORM\Column(type: 'text')]
    #[Groups(groups: ['MessageTemplate:read', 'MessageTemplate:write'])]
    private ?string $body = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['MessageTemplate:read', 'MessageTemplate:write'])]
    private ?Client $client = null;

    #[ORM\Column(nullable: true, options: ['default' => false])]
    #[Groups(groups: ['MessageTemplate:read', 'MessageTemplate:write'])]
    private ?bool $isSmsMessage = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }

    public function getIsSmsMessage(): ?bool
    {
        return $this->isSmsMessage;
    }

    public function setIsSmsMessage(?bool $isSmsMessage): static
    {
        $this->isSmsMessage = $isSmsMessage;
        return $this;
    }
}
