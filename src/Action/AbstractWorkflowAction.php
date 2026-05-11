<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Auth\Content\AuthorAwareInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\SerializerInterface;

abstract readonly class AbstractWorkflowAction
{
    public function __construct(
        protected ContentWorkflowService $workflowService,
        protected EntityManagerInterface $entityManager,
        protected SerializerInterface $serializer,
        protected Security $security,
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

        $entity = $this->entityManager->getRepository($resourceClass)->findOneBy(['slug' => $id])
            ?? $this->entityManager->find($resourceClass, $id);

        if (!$entity instanceof PublicationWorkflowAwareInterface) {
            throw new NotFoundHttpException('Resource not found or does not support workflow.');
        }

        $this->denyAccessUnlessOwnerOrAdmin($entity);

        return $entity;
    }

    private function denyAccessUnlessOwnerOrAdmin(PublicationWorkflowAwareInterface $entity): void
    {
        if ($this->security->isGranted('PERMISSION_CONTENT_EDIT_ALL')) {
            return;
        }

        if ($entity instanceof AuthorAwareInterface && $entity->getAuthor() === $this->security->getUser()) {
            return;
        }

        throw new AccessDeniedHttpException('Access denied.');
    }

    protected function applyTransitionAndPersist(
        PublicationWorkflowAwareInterface $content,
        string $transition,
    ): PublicationWorkflowAwareInterface {
        $this->workflowService->applyTransition($content, $transition);
        $this->entityManager->flush();

        return $content;
    }

    protected function createJsonLdResponse(PublicationWorkflowAwareInterface $content): JsonResponse
    {
        $json = $this->serializer->serialize($content, 'jsonld');

        return new JsonResponse($json, JsonResponse::HTTP_OK, [], true);
    }
}
