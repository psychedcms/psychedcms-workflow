<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentTrait;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowTrait;
use PsychedCms\Workflow\Service\ContentWorkflowService;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;

final class WorkflowTransitionTest extends TestCase
{
    private ContentWorkflowService $service;
    private Registry $registry;

    protected function setUp(): void
    {
        $this->registry = $this->createWorkflowRegistry();
        $this->service = new ContentWorkflowService($this->registry);
    }

    public function testGetWorkflowStateReturnsCurrentPlaceAndAvailableTransitions(): void
    {
        $content = $this->createContent();

        $state = $this->service->getWorkflowState($content);

        $this->assertSame('draft', $state['place']);
        $this->assertContains('submit_for_review', $state['available_transitions']);
        $this->assertContains('publish_from_draft', $state['available_transitions']);
        $this->assertContains('schedule_from_draft', $state['available_transitions']);
        $this->assertContains('archive_from_draft', $state['available_transitions']);
    }

    public function testApplyTransitionSubmitForReview(): void
    {
        $content = $this->createContent();

        $this->service->applyTransition($content, 'submit_for_review');

        $this->assertSame('review', $content->getStatus());
    }

    public function testApplyTransitionRequestChanges(): void
    {
        $content = $this->createContent()->setStatus('review');

        $this->service->applyTransition($content, 'request_changes');

        $this->assertSame('draft', $content->getStatus());
    }

    public function testApplyTransitionApprove(): void
    {
        $content = $this->createContent()->setStatus('review');

        $this->service->applyTransition($content, 'approve');

        $this->assertSame('published', $content->getStatus());
    }

    public function testApplyTransitionPublishFromDraft(): void
    {
        $content = $this->createContent();

        $this->service->applyTransition($content, 'publish_from_draft');

        $this->assertSame('published', $content->getStatus());
    }

    public function testApplyTransitionPublishFromReview(): void
    {
        $content = $this->createContent()->setStatus('review');

        $this->service->applyTransition($content, 'publish_from_review');

        $this->assertSame('published', $content->getStatus());
    }

    public function testApplyTransitionScheduleFromDraft(): void
    {
        $content = $this->createContent();

        $this->service->applyTransition($content, 'schedule_from_draft');

        $this->assertSame('scheduled', $content->getStatus());
    }

    public function testApplyTransitionScheduleFromReview(): void
    {
        $content = $this->createContent()->setStatus('review');

        $this->service->applyTransition($content, 'schedule_from_review');

        $this->assertSame('scheduled', $content->getStatus());
    }

    public function testApplyTransitionAutoPublish(): void
    {
        $content = $this->createContent()->setStatus('scheduled');

        $this->service->applyTransition($content, 'auto_publish');

        $this->assertSame('published', $content->getStatus());
    }

    public function testApplyTransitionUnpublish(): void
    {
        $content = $this->createContent()->setStatus('published');

        $this->service->applyTransition($content, 'unpublish');

        $this->assertSame('draft', $content->getStatus());
    }

    public function testApplyTransitionArchiveFromDraft(): void
    {
        $content = $this->createContent();

        $this->service->applyTransition($content, 'archive_from_draft');

        $this->assertSame('archived', $content->getStatus());
    }

    public function testApplyTransitionArchiveFromPublished(): void
    {
        $content = $this->createContent()->setStatus('published');

        $this->service->applyTransition($content, 'archive_from_published');

        $this->assertSame('archived', $content->getStatus());
    }

    public function testApplyTransitionRestore(): void
    {
        $content = $this->createContent()->setStatus('archived');

        $this->service->applyTransition($content, 'restore');

        $this->assertSame('draft', $content->getStatus());
    }

    public function testInvalidTransitionThrowsException(): void
    {
        $content = $this->createContent();

        $this->expectException(\PsychedCms\Workflow\Exception\InvalidTransitionException::class);
        $this->expectExceptionMessage('Transition "restore" is not available from place "draft"');

        $this->service->applyTransition($content, 'restore');
    }

    public function testCanApplyTransitionReturnsTrue(): void
    {
        $content = $this->createContent();

        $this->assertTrue($this->service->canApplyTransition($content, 'submit_for_review'));
        $this->assertTrue($this->service->canApplyTransition($content, 'publish_from_draft'));
    }

    public function testCanApplyTransitionReturnsFalse(): void
    {
        $content = $this->createContent();

        $this->assertFalse($this->service->canApplyTransition($content, 'restore'));
        $this->assertFalse($this->service->canApplyTransition($content, 'unpublish'));
    }

    public function testFullWorkflowDraftToPublishedToArchived(): void
    {
        $content = $this->createContent();

        $this->assertSame('draft', $content->getStatus());

        $this->service->applyTransition($content, 'submit_for_review');
        $this->assertSame('review', $content->getStatus());

        $this->service->applyTransition($content, 'approve');
        $this->assertSame('published', $content->getStatus());

        $this->service->applyTransition($content, 'archive_from_published');
        $this->assertSame('archived', $content->getStatus());

        $this->service->applyTransition($content, 'restore');
        $this->assertSame('draft', $content->getStatus());
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

    private function createWorkflowRegistry(): Registry
    {
        $definitionBuilder = new DefinitionBuilder();
        $definitionBuilder
            ->addPlaces(['draft', 'review', 'scheduled', 'published', 'archived'])
            ->addTransition(new Transition('submit_for_review', 'draft', 'review'))
            ->addTransition(new Transition('request_changes', 'review', 'draft'))
            ->addTransition(new Transition('approve', 'review', 'published'))
            // Transitions with multiple "from" states need to be added separately for state machines
            ->addTransition(new Transition('publish_from_draft', 'draft', 'published'))
            ->addTransition(new Transition('publish_from_review', 'review', 'published'))
            ->addTransition(new Transition('schedule_from_draft', 'draft', 'scheduled'))
            ->addTransition(new Transition('schedule_from_review', 'review', 'scheduled'))
            ->addTransition(new Transition('auto_publish', 'scheduled', 'published'))
            ->addTransition(new Transition('unpublish', 'published', 'draft'))
            ->addTransition(new Transition('archive_from_draft', 'draft', 'archived'))
            ->addTransition(new Transition('archive_from_published', 'published', 'archived'))
            ->addTransition(new Transition('restore', 'archived', 'draft'))
            ->setInitialPlaces(['draft']);

        $definition = $definitionBuilder->build();

        $markingStore = new MethodMarkingStore(true, 'status');

        $workflow = new StateMachine($definition, $markingStore, name: 'content_publishing');

        $registry = new Registry();
        $registry->addWorkflow($workflow, new InstanceOfSupportStrategy(PublicationWorkflowAwareInterface::class));

        return $registry;
    }
}
