<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Action;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PsychedCms\Workflow\Service\ContentWorkflowService;
use PsychedCms\Workflow\UseCase\SchedulePublication;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ScheduleAction extends AbstractWorkflowAction
{
    public function __construct(
        ContentWorkflowService $workflowService,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        private SchedulePublication $schedulePublication,
    ) {
        parent::__construct($workflowService, $entityManager, $serializer);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $content = $this->getEntityFromRequest($request);
        $scheduledAt = $this->getScheduledAtFromRequest($request);

        $this->schedulePublication->execute($content, $scheduledAt);
        $this->entityManager->flush();

        return $this->createJsonLdResponse($content);
    }

    private function getScheduledAtFromRequest(Request $request): DateTimeImmutable
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['scheduledAt'])) {
            throw new BadRequestHttpException('Missing required field: scheduledAt');
        }

        $scheduledAt = DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $data['scheduledAt']);

        if ($scheduledAt === false) {
            throw new BadRequestHttpException('Invalid date format for scheduledAt. Expected ISO 8601 format.');
        }

        if ($scheduledAt <= new DateTimeImmutable()) {
            throw new BadRequestHttpException('scheduledAt must be in the future.');
        }

        return $scheduledAt;
    }
}
