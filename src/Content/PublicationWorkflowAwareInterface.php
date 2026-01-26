<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Content;

use DateTimeImmutable;
use PsychedCms\Core\Content\ContentInterface;

interface PublicationWorkflowAwareInterface extends ContentInterface
{
    public function getStatus(): string;

    public function setStatus(string $status): static;

    public function getPublishedAt(): ?DateTimeImmutable;

    public function setPublishedAt(?DateTimeImmutable $publishedAt): static;

    public function getDepublishedAt(): ?DateTimeImmutable;

    public function setDepublishedAt(?DateTimeImmutable $depublishedAt): static;
}
