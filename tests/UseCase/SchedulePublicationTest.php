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
use PsychedCms\Workflow\UseCase\SchedulePublication;

class SchedulePublicationTest extends TestCase
{
    private ContentWorkflowServiceInterface&MockObject $workflowService;
    private EntityManagerInterface&MockObject $entityManager;
    private ScheduledPublicationRepositoryInterface&MockObject $repository;
    private SchedulePublication $useCase;

    protected function setUp(): void
    {
        $this->workflowService = $this->createMock(ContentWorkflowServiceInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(ScheduledPublicationRepositoryInterface::class);

        $this->useCase = new SchedulePublication(
            $this->workflowService,
            $this->entityManager,
            $this->repository
        );
    }

    public function testExecuteCreatesCalendarEventAndAppliesTransition(): void
    {
        $content = new TestContent(1);
        $publishAt = new DateTimeImmutable('+1 day');

        $this->repository
            ->expects($this->once())
            ->method('findByTarget')
            ->with(TestContent::class, 1)
            ->willReturn(null);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PublishContentEvent::class));

        $this->workflowService
            ->expects($this->once())
            ->method('applyTransition')
            ->with($content, 'schedule');

        $event = $this->useCase->execute($content, $publishAt);

        $this->assertInstanceOf(PublishContentEvent::class, $event);
        $this->assertSame(TestContent::class, $event->getTargetClass());
        $this->assertSame(1, $event->getTargetId());
        $this->assertSame($publishAt, $event->getScheduledAt());
        $this->assertSame($publishAt, $content->getPublishedAt());
    }

    public function testExecuteRemovesExistingScheduledPublication(): void
    {
        $content = new TestContent(1);
        $publishAt = new DateTimeImmutable('+1 day');
        $existingEvent = new PublishContentEvent(TestContent::class, 1, new DateTimeImmutable('+2 days'));

        $this->repository
            ->expects($this->once())
            ->method('findByTarget')
            ->with(TestContent::class, 1)
            ->willReturn($existingEvent);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($existingEvent);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PublishContentEvent::class));

        $this->workflowService
            ->expects($this->once())
            ->method('applyTransition');

        $this->useCase->execute($content, $publishAt);
    }

    public function testExecuteThrowsExceptionForUnpersistedContent(): void
    {
        $content = new TestContent(null);
        $publishAt = new DateTimeImmutable('+1 day');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Content must be persisted before scheduling publication.');

        $this->useCase->execute($content, $publishAt);
    }
}
