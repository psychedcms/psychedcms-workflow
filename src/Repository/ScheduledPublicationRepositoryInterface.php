<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Repository;

use DateTimeImmutable;
use PsychedCms\Workflow\Calendar\PublishContentEvent;

interface ScheduledPublicationRepositoryInterface
{
    /**
     * Find a scheduled publication event for a specific target.
     */
    public function findByTarget(string $class, int|string $id): ?PublishContentEvent;

    /**
     * Find all scheduled publications within a date range.
     *
     * @return iterable<PublishContentEvent>
     */
    public function findScheduledPublications(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?string $contentClass = null,
    ): iterable;
}
