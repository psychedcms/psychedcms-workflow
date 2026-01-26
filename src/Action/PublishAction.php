<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

final readonly class PublishAction extends AbstractWorkflowAction
{
    public function __invoke(PublicationWorkflowAwareInterface $data): PublicationWorkflowAwareInterface
    {
        return $this->applyTransitionAndPersist($data, 'publish');
    }
}
