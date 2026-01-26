<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;

abstract readonly class AbstractWorkflowAction
{
    public function __construct(
        protected ContentWorkflowService $workflowService,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    protected function applyTransitionAndPersist(
        PublicationWorkflowAwareInterface $content,
        string $transition,
    ): PublicationWorkflowAwareInterface {
        $this->workflowService->applyTransition($content, $transition);
        $this->entityManager->flush();

        return $content;
    }
}
