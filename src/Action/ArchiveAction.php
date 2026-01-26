<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

final readonly class ArchiveAction extends AbstractWorkflowAction
{
    public function __invoke(PublicationWorkflowAwareInterface $data): PublicationWorkflowAwareInterface
    {
        return $this->applyTransitionAndPersist($data, 'archive');
    }
}
