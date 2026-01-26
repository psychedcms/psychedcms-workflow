<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

final readonly class SubmitForReviewAction extends AbstractWorkflowAction
{
    public function __invoke(PublicationWorkflowAwareInterface $data): PublicationWorkflowAwareInterface
    {
        return $this->applyTransitionAndPersist($data, 'submit_for_review');
    }
}
