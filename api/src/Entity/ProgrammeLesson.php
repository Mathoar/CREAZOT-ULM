<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Delete;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

#[ORM\Entity]
#[ORM\Table(name: 'programme_lesson')]
#[ORM\UniqueConstraint(name: 'unique_programme_lesson', columns: ['programme_id', 'lesson_id'])]
#[ApiResource(
    uriTemplate: '/programme_lessons{._format}',
    operations: [
        new GetCollection(
            itemUriTemplate: '/programme_lessons/{id}{._format}',
        ),
        new Post(
            itemUriTemplate: '/programme_lessons/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Get(
            uriTemplate: '/programme_lessons/{id}{._format}'
        ),
        new Put(
            uriTemplate: '/programme_lessons/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
        new Delete(
            uriTemplate: '/programme_lessons/{id}{._format}',
            security: 'is_granted("OIDC_ADMIN")'
        ),
    ],
    normalizationContext: [
        AbstractNormalizer::GROUPS => ['ProgrammeLesson:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ],
    denormalizationContext: [
        AbstractNormalizer::GROUPS => ['ProgrammeLesson:write'],
    ],
    collectDenormalizationErrors: true,
    security: 'is_granted("OIDC_USER")',
)]
class ProgrammeLesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['ProgrammeLesson:read', 'Programme:read', 'Programme:write'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Programme::class, inversedBy: 'programmeLessons')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(groups: ['ProgrammeLesson:read', 'ProgrammeLesson:write'])]
    private ?Programme $programme = null;

    #[ORM\ManyToOne(targetEntity: Lesson::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(groups: ['ProgrammeLesson:read', 'ProgrammeLesson:write', 'Programme:read', 'Programme:write'])]
    private ?Lesson $lesson = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    #[Groups(groups: ['ProgrammeLesson:read', 'ProgrammeLesson:write', 'Programme:read', 'Programme:write'])]
    private int $position = 0;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): static
    {
        $this->position = $position;
        return $this;
    }
}
