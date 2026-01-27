<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final readonly class RestoreAction extends AbstractWorkflowAction
{
    public function __invoke(Request $request): JsonResponse
    {
        $content = $this->applyTransitionAndPersist($this->getEntityFromRequest($request), 'restore');

        return $this->createJsonLdResponse($content);
    }
}
