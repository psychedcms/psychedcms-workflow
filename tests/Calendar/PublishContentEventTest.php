<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Calendar;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PsychedCms\Workflow\Calendar\PublishContentEvent;

class PublishContentEventTest extends TestCase
{
    public function testConstructorSetsProperties(): void
    {
        $scheduledAt = new DateTimeImmutable('2026-02-01 10:00:00');
        $event = new PublishContentEvent('App\\Entity\\Post', 123, $scheduledAt);

        $this->assertSame('App\\Entity\\Post', $event->getTargetClass());
        $this->assertSame(123, $event->getTargetId());
        $this->assertSame($scheduledAt, $event->getScheduledAt());
    }

    public function testGetEventTypeReturnsPublishContent(): void
    {
        $event = new PublishContentEvent('App\\Entity\\Post', 1, new DateTimeImmutable());

        $this->assertSame('publish_content', $event->getEventType());
    }

    public function testIsProcessedReturnsFalseByDefault(): void
    {
        $event = new PublishContentEvent('App\\Entity\\Post', 1, new DateTimeImmutable());

        $this->assertFalse($event->isProcessed());
        $this->assertNull($event->getProcessedAt());
    }

    public function testMarkProcessedSetsProcessedAt(): void
    {
        $event = new PublishContentEvent('App\\Entity\\Post', 1, new DateTimeImmutable());

        $before = new DateTimeImmutable();
        $event->markProcessed();
        $after = new DateTimeImmutable();

        $this->assertTrue($event->isProcessed());
        $this->assertNotNull($event->getProcessedAt());
        $this->assertGreaterThanOrEqual($before, $event->getProcessedAt());
        $this->assertLessThanOrEqual($after, $event->getProcessedAt());
    }

    public function testTargetIdCanBeString(): void
    {
        $uuid = '550e8400-e29b-41d4-a716-446655440000';
        $event = new PublishContentEvent('App\\Entity\\Post', $uuid, new DateTimeImmutable());

        $this->assertSame($uuid, $event->getTargetId());
    }
}
