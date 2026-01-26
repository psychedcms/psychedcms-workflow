<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\UseCase;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;

final readonly class Restore
{
    public function __construct(
        private ContentWorkflowService $workflowService,
    ) {
    }

    public function execute(PublicationWorkflowAwareInterface $content): void
    {
        $this->workflowService->applyTransition($content, 'restore');
    }
}
