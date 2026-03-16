<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Fixtures;

use DateTimeImmutable;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use Symfony\Component\Uid\Ulid;

class TestContent implements PublicationWorkflowAwareInterface
{
    private ?Ulid $id;
    private string $status = 'draft';
    private ?DateTimeImmutable $publishedAt = null;
    private ?DateTimeImmutable $depublishedAt = null;
    private ?string $slug = null;
    private ?DateTimeImmutable $createdAt = null;
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(?Ulid $id = null, bool $generateId = true)
    {
        $this->id = $id ?? ($generateId ? new Ulid() : null);
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getAuthor(): ?object
    {
        return null;
    }
}
