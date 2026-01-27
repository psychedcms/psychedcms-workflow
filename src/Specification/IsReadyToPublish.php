<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Specification;

use DateTimeImmutable;
use PsychedCms\Core\Domain\Specification\AbstractSpecification;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

/**
 * Checks if content is scheduled and ready to be auto-published.
 *
 * Content is ready when:
 * - Status is 'scheduled'
 * - publishedAt is set
 * - publishedAt is in the past or now
 */
final class IsReadyToPublish extends AbstractSpecification
{
    public function __construct(
        private readonly ?DateTimeImmutable $referenceTime = null,
    ) {
    }

    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof PublicationWorkflowAwareInterface) {
            return false;
        }

        if ($candidate->getStatus() !== 'scheduled') {
            return false;
        }

        $publishedAt = $candidate->getPublishedAt();
        if ($publishedAt === null) {
            return false;
        }

        $now = $this->referenceTime ?? new DateTimeImmutable();

        return $publishedAt <= $now;
    }
}
