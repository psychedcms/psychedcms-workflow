<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Calendar;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Calendar\Entity\CalendarEventInterface;
use PsychedCms\Calendar\Handler\CalendarEventHandlerInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\UseCase\AutoPublishInterface;

/**
 * Handles PublishContentEvent by auto-publishing the target content.
 */
final readonly class PublishContentEventHandler implements CalendarEventHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AutoPublishInterface $autoPublish,
    ) {
    }

    public function supports(CalendarEventInterface $event): bool
    {
        return $event instanceof PublishContentEvent && !$event->isProcessed();
    }

    public function handle(CalendarEventInterface $event): void
    {
        assert($event instanceof PublishContentEvent);

        $content = $this->entityManager->find(
            $event->getTargetClass(),
            $event->getTargetId(),
        );

        if ($content instanceof PublicationWorkflowAwareInterface) {
            $this->autoPublish->execute($content);
            $event->markProcessed();
        }
    }
}
