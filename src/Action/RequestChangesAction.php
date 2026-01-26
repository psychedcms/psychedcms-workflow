<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

final readonly class RequestChangesAction extends AbstractWorkflowAction
{
    public function __invoke(PublicationWorkflowAwareInterface $data): PublicationWorkflowAwareInterface
    {
        return $this->applyTransitionAndPersist($data, 'request_changes');
    }
}
