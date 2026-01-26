<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class GetWorkflowStateAction
{
    public function __construct(
        private ContentWorkflowService $workflowService,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = $this->getEntityFromRequest($request);
        $state = $this->workflowService->getWorkflowState($data);

        return new JsonResponse($state);
    }

    private function getEntityFromRequest(Request $request): PublicationWorkflowAwareInterface
    {
        // First try API Platform's data attribute
        $data = $request->attributes->get('data');
        if ($data instanceof PublicationWorkflowAwareInterface) {
            return $data;
        }

        // Fallback: fetch entity using route parameters
        $id = $request->attributes->get('id');
        $resourceClass = $request->attributes->get('_api_resource_class');

        if (!$id || !$resourceClass) {
            throw new NotFoundHttpException('Resource not found or does not support workflow.');
        }

        $entity = $this->entityManager->find($resourceClass, $id);

        if (!$entity instanceof PublicationWorkflowAwareInterface) {
            throw new NotFoundHttpException('Resource not found or does not support workflow.');
        }

        return $entity;
    }
}
