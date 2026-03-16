<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Content;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Provides the status marking store and publication timestamps for the content publishing workflow.
 *
 * Use this trait in entities that implement PublicationWorkflowAwareInterface.
 */
trait PublicationWorkflowTrait
{
    #[ORM\Column(length: 32)]
    #[Groups(['content:read', 'content:write'])]
    private string $status = 'draft';

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['content:read'])]
    private ?DateTimeImmutable $publishedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    #[Groups(['content:read'])]
    private ?DateTimeImmutable $depublishedAt = null;

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getDepublishedAt(): ?DateTimeImmutable
    {
        return $this->depublishedAt;
    }

    public function setDepublishedAt(?DateTimeImmutable $depublishedAt): static
    {
        $this->depublishedAt = $depublishedAt;

        return $this;
    }
}
