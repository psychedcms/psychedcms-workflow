<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\UseCase;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Calendar\PublishContentEvent;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Repository\ScheduledPublicationRepositoryInterface;
use PsychedCms\Workflow\Service\ContentWorkflowServiceInterface;

/**
 * Use case for scheduling content publication.
 */
final readonly class SchedulePublication
{
    public function __construct(
        private ContentWorkflowServiceInterface $workflowService,
        private EntityManagerInterface $entityManager,
        private ScheduledPublicationRepositoryInterface $repository,
    ) {
    }

    public function execute(
        PublicationWorkflowAwareInterface $content,
        DateTimeImmutable $publishAt,
    ): PublishContentEvent {
        $contentId = $content->getId();
        if ($contentId === null) {
            throw new \InvalidArgumentException('Content must be persisted before scheduling publication.');
        }

        // Remove existing scheduled publication for this content
        $existing = $this->repository->findByTarget($content::class, $contentId);
        if ($existing !== null) {
            $this->entityManager->remove($existing);
        }

        // Create new calendar event
        $event = new PublishContentEvent($content::class, $contentId, $publishAt);
        $this->entityManager->persist($event);

        // Apply workflow transition (publishedAt stays empty until actual publication)
        $this->workflowService->applyTransition($content, 'schedule');

        return $event;
    }
}
