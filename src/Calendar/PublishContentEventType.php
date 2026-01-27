<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Calendar;

use PsychedCms\Calendar\Doctrine\CalendarEventTypeInterface;

/**
 * Registers the PublishContentEvent in the calendar discriminator map.
 */
final readonly class PublishContentEventType implements CalendarEventTypeInterface
{
    public function getEventType(): string
    {
        return 'publish_content';
    }

    public function getEntityClass(): string
    {
        return PublishContentEvent::class;
    }
}
