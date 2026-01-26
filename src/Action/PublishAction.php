<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class PublishAction extends AbstractWorkflowAction
{
    public function __invoke(Request $request): PublicationWorkflowAwareInterface
    {
        return $this->applyTransitionAndPersist($this->getEntityFromRequest($request), 'publish');
    }
}
