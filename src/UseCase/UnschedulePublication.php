<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\UseCase;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Repository\ScheduledPublicationRepositoryInterface;
use PsychedCms\Workflow\Service\ContentWorkflowServiceInterface;

/**
 * Use case for unscheduling content publication.
 * Returns content to draft and hard deletes the scheduled publication event.
 */
final readonly class UnschedulePublication
{
    public function __construct(
        private ContentWorkflowServiceInterface $workflowService,
        private EntityManagerInterface $entityManager,
        private ScheduledPublicationRepositoryInterface $repository,
    ) {
    }

    public function execute(PublicationWorkflowAwareInterface $content): void
    {
        $contentId = $content->getId();
        if ($contentId === null) {
            throw new \InvalidArgumentException('Content must be persisted before unscheduling.');
        }

        // Hard delete the scheduled publication event
        $existing = $this->repository->findByTarget($content::class, $contentId);
        if ($existing !== null) {
            $this->entityManager->remove($existing);
        }

        // Apply workflow transition back to draft
        $this->workflowService->applyTransition($content, 'unschedule');
    }
}
