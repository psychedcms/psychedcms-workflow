<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class PublishAction extends AbstractWorkflowAction
{
    public function __invoke(Request $request): JsonResponse
    {
        $content = $this->applyTransitionAndPersist($this->getEntityFromRequest($request), 'publish');

        return $this->createJsonLdResponse($content);
    }
}
