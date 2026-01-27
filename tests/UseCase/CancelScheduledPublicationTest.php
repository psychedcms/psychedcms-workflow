<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\UseCase;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsychedCms\Workflow\Calendar\PublishContentEvent;
use PsychedCms\Workflow\Repository\ScheduledPublicationRepositoryInterface;
use PsychedCms\Workflow\Service\ContentWorkflowServiceInterface;
use PsychedCms\Workflow\Tests\Fixtures\TestContent;
use PsychedCms\Workflow\UseCase\CancelScheduledPublication;

class CancelScheduledPublicationTest extends TestCase
{
    private ContentWorkflowServiceInterface&MockObject $workflowService;
    private EntityManagerInterface&MockObject $entityManager;
    private ScheduledPublicationRepositoryInterface&MockObject $repository;
    private CancelScheduledPublication $useCase;

    protected function setUp(): void
    {
        $this->workflowService = $this->createMock(ContentWorkflowServiceInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(ScheduledPublicationRepositoryInterface::class);

        $this->useCase = new CancelScheduledPublication(
            $this->workflowService,
            $this->entityManager,
            $this->repository
        );
    }

    public function testExecuteRemovesEventAndAppliesTransition(): void
    {
        $content = new TestContent(1);
        $content->setPublishedAt(new DateTimeImmutable('+1 day'));

        $existingEvent = new PublishContentEvent(TestContent::class, 1, new DateTimeImmutable('+1 day'));

        $this->repository
            ->expects($this->once())
            ->method('findByTarget')
            ->with(TestContent::class, 1)
            ->willReturn($existingEvent);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($existingEvent);

        $this->workflowService
            ->expects($this->once())
            ->method('applyTransition')
            ->with($content, 'unschedule');

        $this->useCase->execute($content);

        $this->assertNull($content->getPublishedAt());
    }

    public function testExecuteHandlesNoExistingEvent(): void
    {
        $content = new TestContent(1);
        $content->setPublishedAt(new DateTimeImmutable('+1 day'));

        $this->repository
            ->expects($this->once())
            ->method('findByTarget')
            ->with(TestContent::class, 1)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->never())
            ->method('remove');

        $this->workflowService
            ->expects($this->once())
            ->method('applyTransition')
            ->with($content, 'unschedule');

        $this->useCase->execute($content);

        $this->assertNull($content->getPublishedAt());
    }

    public function testExecuteReturnsEarlyForUnpersistedContent(): void
    {
        $content = new TestContent(null);

        $this->repository
            ->expects($this->never())
            ->method('findByTarget');

        $this->entityManager
            ->expects($this->never())
            ->method('remove');

        $this->workflowService
            ->expects($this->never())
            ->method('applyTransition');

        $this->useCase->execute($content);
    }
}
