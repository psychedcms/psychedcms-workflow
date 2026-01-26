<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\EventListener;

use DateTimeImmutable;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;

final class WorkflowTransitionListener
{
    /**
     * Handle publish, approve, and auto_publish transitions.
     * Sets the publishedAt timestamp if not already set.
     */
    public function onPublish(CompletedEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof PublicationWorkflowAwareInterface) {
            return;
        }

        if ($subject->getPublishedAt() === null) {
            $subject->setPublishedAt(new DateTimeImmutable());
        }

        $subject->setDepublishedAt(null);
    }

    /**
     * Handle unpublish transition.
     * Sets the depublishedAt timestamp.
     */
    public function onUnpublish(CompletedEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof PublicationWorkflowAwareInterface) {
            return;
        }

        $subject->setDepublishedAt(new DateTimeImmutable());
    }

    /**
     * Handle archive transition.
     * Sets the depublishedAt timestamp.
     */
    public function onArchive(CompletedEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof PublicationWorkflowAwareInterface) {
            return;
        }

        $subject->setDepublishedAt(new DateTimeImmutable());
    }

    /**
     * Handle restore transition.
     * Clears both publishedAt and depublishedAt timestamps.
     */
    public function onRestore(CompletedEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof PublicationWorkflowAwareInterface) {
            return;
        }

        $subject->setPublishedAt(null);
        $subject->setDepublishedAt(null);
    }
}
