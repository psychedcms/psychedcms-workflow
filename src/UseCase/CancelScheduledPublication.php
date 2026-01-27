<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\UseCase;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Repository\ScheduledPublicationRepositoryInterface;
use PsychedCms\Workflow\Service\ContentWorkflowServiceInterface;

/**
 * Use case for canceling a scheduled publication.
 */
final readonly class CancelScheduledPublication
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
            return;
        }

        // Remove the scheduled publication event
        $existing = $this->repository->findByTarget($content::class, $contentId);
        if ($existing !== null) {
            $this->entityManager->remove($existing);
        }

        // Apply workflow transition
        $this->workflowService->applyTransition($content, 'unschedule');
        $content->setPublishedAt(null);
    }
}
