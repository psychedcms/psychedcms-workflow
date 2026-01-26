<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;

final readonly class GetWorkflowStateAction
{
    public function __construct(
        private ContentWorkflowService $workflowService,
    ) {
    }

    /**
     * @return array{place: string, available_transitions: list<string>}
     */
    public function __invoke(PublicationWorkflowAwareInterface $data): array
    {
        return $this->workflowService->getWorkflowState($data);
    }
}
