<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Calendar;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use PsychedCms\Calendar\Entity\AbstractCalendarEvent;

/**
 * Calendar event for scheduling content publication.
 */
#[ORM\Entity]
class PublishContentEvent extends AbstractCalendarEvent
{
    #[ORM\Column(length: 255)]
    private string $targetClass;

    #[ORM\Column(type: Types::STRING, length: 36)]
    private int|string $targetId;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $processedAt = null;

    public function __construct(
        string $targetClass,
        int|string $targetId,
        DateTimeImmutable $scheduledAt,
    ) {
        parent::__construct($scheduledAt);
        $this->targetClass = $targetClass;
        $this->targetId = $targetId;
    }

    public function getEventType(): string
    {
        return 'publish_content';
    }

    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    public function getTargetId(): int|string
    {
        return $this->targetId;
    }

    public function isProcessed(): bool
    {
        return $this->processedAt !== null;
    }

    public function getProcessedAt(): ?DateTimeImmutable
    {
        return $this->processedAt;
    }

    public function markProcessed(): void
    {
        $this->processedAt = new DateTimeImmutable();
    }
}
