<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Specification;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentTrait;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowTrait;
use PsychedCms\Workflow\Specification\IsArchived;
use PsychedCms\Workflow\Specification\IsDraft;
use PsychedCms\Workflow\Specification\IsInReview;
use PsychedCms\Workflow\Specification\IsPublished;
use PsychedCms\Workflow\Specification\IsScheduled;

final class PublicationStatusSpecificationTest extends TestCase
{
    public function testIsDraftReturnsTrueOnlyWhenStatusIsDraft(): void
    {
        $spec = new IsDraft();

        $draftContent = $this->createContent()->setStatus('draft');
        $this->assertTrue($spec->isSatisfiedBy($draftContent));

        $publishedContent = $this->createContent()->setStatus('published');
        $this->assertFalse($spec->isSatisfiedBy($publishedContent));

        $archivedContent = $this->createContent()->setStatus('archived');
        $this->assertFalse($spec->isSatisfiedBy($archivedContent));
    }

    public function testIsInReviewReturnsTrueOnlyWhenStatusIsReview(): void
    {
        $spec = new IsInReview();

        $reviewContent = $this->createContent()->setStatus('review');
        $this->assertTrue($spec->isSatisfiedBy($reviewContent));

        $draftContent = $this->createContent()->setStatus('draft');
        $this->assertFalse($spec->isSatisfiedBy($draftContent));

        $publishedContent = $this->createContent()->setStatus('published');
        $this->assertFalse($spec->isSatisfiedBy($publishedContent));
    }

    public function testIsPublishedReturnsTrueWhenPublishedAndNotDepublished(): void
    {
        $content = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));

        $spec = new IsPublished();

        $this->assertTrue($spec->isSatisfiedBy($content));

        $contentWithFutureDepublish = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'))
            ->setDepublishedAt(new DateTimeImmutable('+1 hour'));

        $this->assertTrue($spec->isSatisfiedBy($contentWithFutureDepublish));
    }

    public function testIsPublishedReturnsFalseWhenDepublishedInPast(): void
    {
        $content = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('-2 hours'))
            ->setDepublishedAt(new DateTimeImmutable('-1 hour'));

        $spec = new IsPublished();

        $this->assertFalse($spec->isSatisfiedBy($content));
    }

    public function testIsPublishedReturnsFalseWhenPublishedAtInFuture(): void
    {
        $content = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('+1 hour'));

        $spec = new IsPublished();

        $this->assertFalse($spec->isSatisfiedBy($content));
    }

    public function testIsScheduledReturnsTrueWhenStatusIsScheduledAndPublishedAtInFuture(): void
    {
        $spec = new IsScheduled();

        $scheduledContent = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt(new DateTimeImmutable('+1 hour'));
        $this->assertTrue($spec->isSatisfiedBy($scheduledContent));

        $scheduledPastContent = $this->createContent()
            ->setStatus('scheduled')
            ->setPublishedAt(new DateTimeImmutable('-1 hour'));
        $this->assertFalse($spec->isSatisfiedBy($scheduledPastContent));

        $publishedContent = $this->createContent()
            ->setStatus('published')
            ->setPublishedAt(new DateTimeImmutable('+1 hour'));
        $this->assertFalse($spec->isSatisfiedBy($publishedContent));
    }

    public function testIsArchivedReturnsTrueOnlyWhenStatusIsArchived(): void
    {
        $spec = new IsArchived();

        $archivedContent = $this->createContent()->setStatus('archived');
        $this->assertTrue($spec->isSatisfiedBy($archivedContent));

        $draftContent = $this->createContent()->setStatus('draft');
        $this->assertFalse($spec->isSatisfiedBy($draftContent));

        $publishedContent = $this->createContent()->setStatus('published');
        $this->assertFalse($spec->isSatisfiedBy($publishedContent));
    }

    public function testSpecificationsReturnFalseForNonWorkflowAwareContent(): void
    {
        $nonWorkflowContent = new \stdClass();

        $this->assertFalse((new IsDraft())->isSatisfiedBy($nonWorkflowContent));
        $this->assertFalse((new IsInReview())->isSatisfiedBy($nonWorkflowContent));
        $this->assertFalse((new IsPublished())->isSatisfiedBy($nonWorkflowContent));
        $this->assertFalse((new IsScheduled())->isSatisfiedBy($nonWorkflowContent));
        $this->assertFalse((new IsArchived())->isSatisfiedBy($nonWorkflowContent));
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
