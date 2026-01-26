<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

abstract readonly class AbstractWorkflowAction
{
    public function __construct(
        protected ContentWorkflowService $workflowService,
        protected EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Extract the entity from API Platform's request attributes or fetch it directly.
     */
    protected function getEntityFromRequest(Request $request): PublicationWorkflowAwareInterface
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

    protected function applyTransitionAndPersist(
        PublicationWorkflowAwareInterface $content,
        string $transition,
    ): PublicationWorkflowAwareInterface {
        $this->workflowService->applyTransition($content, $transition);
        $this->entityManager->flush();

        return $content;
    }
}
