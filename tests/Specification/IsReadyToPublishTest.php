<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Specification;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentTrait;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowTrait;
use PsychedCms\Workflow\Specification\IsReadyToPublish;

final class IsReadyToPublishTest extends TestCase
{
    public function testReturnsTrueWhenScheduledAndPublishedAtInPast(): void
    {
        $referenceTime = new DateTimeImmutable('2024-01-15 12:00:00');

        $content = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt(new DateTimeImmutable('2024-01-15 11:00:00')); // 1 hour before reference

        $spec = new IsReadyToPublish($referenceTime);

        $this->assertTrue($spec->isSatisfiedBy($content));
    }

    public function testReturnsTrueWhenScheduledAndPublishedAtEqualsNow(): void
    {
        $referenceTime = new DateTimeImmutable('2024-01-15 12:00:00');

        $content = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt($referenceTime);

        $spec = new IsReadyToPublish($referenceTime);

        $this->assertTrue($spec->isSatisfiedBy($content));
    }

    public function testReturnsFalseWhenScheduledButPublishedAtInFuture(): void
    {
        $referenceTime = new DateTimeImmutable('2024-01-15 12:00:00');

        $content = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt(new DateTimeImmutable('2024-01-15 13:00:00')); // 1 hour after reference

        $spec = new IsReadyToPublish($referenceTime);

        $this->assertFalse($spec->isSatisfiedBy($content));
    }

    public function testReturnsFalseWhenStatusIsNotScheduled(): void
    {
        $referenceTime = new DateTimeImmutable('2024-01-15 12:00:00');

        $content = $this->createContent()
            ->setStatus('draft')
            ->setPublishedAt(new DateTimeImmutable('2024-01-15 11:00:00'));

        $spec = new IsReadyToPublish($referenceTime);

        $this->assertFalse($spec->isSatisfiedBy($content));
    }

    public function testReturnsFalseWhenPublishedAtIsNull(): void
    {
        $referenceTime = new DateTimeImmutable('2024-01-15 12:00:00');

        $content = $this->createContent()
            ->setStatus('scheduled');

        $spec = new IsReadyToPublish($referenceTime);

        $this->assertFalse($spec->isSatisfiedBy($content));
    }

    public function testReturnsFalseForNonWorkflowAwareContent(): void
    {
        $spec = new IsReadyToPublish();

        $this->assertFalse($spec->isSatisfiedBy(new \stdClass()));
    }

    public function testUsesCurrentTimeWhenNoReferenceTimeProvided(): void
    {
        $content = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));

        $spec = new IsReadyToPublish();

        $this->assertTrue($spec->isSatisfiedBy($content));
    }

    private function createContent(): PublicationWorkflowAwareInterface
    {
        return new class implements PublicationWorkflowAwareInterface {
            use ContentTrait;
            use PublicationWorkflowTrait;

            public function getAuthor(): ?object
            {
                return null;
            }
        };
    }
}
