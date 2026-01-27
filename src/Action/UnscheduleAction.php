<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;
use PsychedCms\Workflow\UseCase\UnschedulePublication;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class UnscheduleAction extends AbstractWorkflowAction
{
    public function __construct(
        ContentWorkflowService $workflowService,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        private UnschedulePublication $unschedulePublication,
    ) {
        parent::__construct($workflowService, $entityManager, $serializer);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $content = $this->getEntityFromRequest($request);

        $this->unschedulePublication->execute($content);
        $this->entityManager->flush();

        return $this->createJsonLdResponse($content);
    }
}
