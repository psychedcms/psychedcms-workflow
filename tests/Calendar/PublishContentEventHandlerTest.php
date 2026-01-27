<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Calendar;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsychedCms\Calendar\Entity\AbstractCalendarEvent;
use PsychedCms\Workflow\Calendar\PublishContentEvent;
use PsychedCms\Workflow\Calendar\PublishContentEventHandler;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\UseCase\AutoPublishInterface;

class PublishContentEventHandlerTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private AutoPublishInterface&MockObject $autoPublish;
    private PublishContentEventHandler $handler;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->autoPublish = $this->createMock(AutoPublishInterface::class);
        $this->handler = new PublishContentEventHandler($this->entityManager, $this->autoPublish);
    }

    public function testSupportsReturnsTrueForUnprocessedPublishContentEvent(): void
    {
        $event = new PublishContentEvent('App\\Entity\\Post', 1, new DateTimeImmutable());

        $this->assertTrue($this->handler->supports($event));
    }

    public function testSupportsReturnsFalseForProcessedEvent(): void
    {
        $event = new PublishContentEvent('App\\Entity\\Post', 1, new DateTimeImmutable());
        $event->markProcessed();

        $this->assertFalse($this->handler->supports($event));
    }

    public function testSupportsReturnsFalseForOtherEventTypes(): void
    {
        $event = $this->createMock(AbstractCalendarEvent::class);

        $this->assertFalse($this->handler->supports($event));
    }

    public function testHandlePublishesContentAndMarksProcessed(): void
    {
        $content = $this->createMock(PublicationWorkflowAwareInterface::class);
        $event = new PublishContentEvent('App\\Entity\\Post', 1, new DateTimeImmutable());

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->with('App\\Entity\\Post', 1)
            ->willReturn($content);

        $this->autoPublish
            ->expects($this->once())
            ->method('execute')
            ->with($content);

        $this->handler->handle($event);

        $this->assertTrue($event->isProcessed());
    }

    public function testHandleDoesNothingWhenContentNotFound(): void
    {
        $event = new PublishContentEvent('App\\Entity\\Post', 999, new DateTimeImmutable());

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->willReturn(null);

        $this->autoPublish
            ->expects($this->never())
            ->method('execute');

        $this->handler->handle($event);

        $this->assertFalse($event->isProcessed());
    }

    public function testHandleDoesNothingWhenContentNotWorkflowAware(): void
    {
        $content = new \stdClass();
        $event = new PublishContentEvent('App\\Entity\\Post', 1, new DateTimeImmutable());

        $this->entityManager
            ->expects($this->once())
            ->method('find')
            ->willReturn($content);

        $this->autoPublish
            ->expects($this->never())
            ->method('execute');

        $this->handler->handle($event);

        $this->assertFalse($event->isProcessed());
    }
}
