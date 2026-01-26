<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Specification;

use DateTimeImmutable;
use PsychedCms\Core\Domain\Specification\AbstractSpecification;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

final class IsPublished extends AbstractSpecification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof PublicationWorkflowAwareInterface) {
            return false;
        }

        if ($candidate->getStatus() !== 'published') {
            return false;
        }

        $publishedAt = $candidate->getPublishedAt();
        if ($publishedAt === null) {
            return false;
        }

        $now = new DateTimeImmutable();
        if ($publishedAt > $now) {
            return false;
        }

        $depublishedAt = $candidate->getDepublishedAt();
        if ($depublishedAt !== null && $depublishedAt <= $now) {
            return false;
        }

        return true;
    }
}
