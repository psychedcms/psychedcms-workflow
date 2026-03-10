<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Fixtures;

use DateTimeImmutable;
use PsychedCms\Core\Content\UserInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

class TestContent implements PublicationWorkflowAwareInterface
{
    private ?int $id;
    private string $status = 'draft';
    private ?DateTimeImmutable $publishedAt = null;
    private ?DateTimeImmutable $depublishedAt = null;
    private ?string $slug = null;
    private ?DateTimeImmutable $createdAt = null;
    private ?DateTimeImmutable $updatedAt = null;

    public function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
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

    public function getAuthor(): ?UserInterface
    {
        return null;
    }
}
