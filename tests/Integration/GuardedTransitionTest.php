<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Integration;

use PHPUnit\Framework\TestCase;
use PsychedCms\Core\Content\ContentTrait;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Content\PublicationWorkflowTrait;
use PsychedCms\Workflow\Exception\TransitionBlockedException;
use PsychedCms\Workflow\Service\ContentWorkflowService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Event\GuardEvent;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\StateMachine;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\TransitionBlocker;

/**
 * Asserts the publish guard returns a structured, per-field exception that
 * the API layer can turn into a 422 ConstraintViolationList — instead of
 * the previous behaviour where a blocked transition leaked as a 500
 * "Internal Server Error" with no hint about which field failed.
 */
final class GuardedTransitionTest extends TestCase
{
    public function testTransitionBlockedByGuardThrowsStructuredException(): void
    {
        $dispatcher = new EventDispatcher();
        // Block the publish_from_draft transition with two field-level
        // violations carrying property_path + message in the blocker
        // parameters — same shape WorkflowGuardListener produces.
        $dispatcher->addListener(
            'workflow.content_publishing.guard.publish_from_draft',
            static function (GuardEvent $event): void {
                $event->addTransitionBlocker(new TransitionBlocker(
                    'description: This value should not be blank.',
                    'validation_failed',
                    [
                        'property_path' => 'description',
                        'message' => 'This value should not be blank.',
                    ],
                ));
                $event->addTransitionBlocker(new TransitionBlocker(
                    'website: This value should be a valid URL.',
                    'validation_failed',
                    [
                        'property_path' => 'website',
                        'message' => 'This value should be a valid URL.',
                    ],
                ));
            }
        );

        $service = new ContentWorkflowService($this->createWorkflowRegistry($dispatcher));
        $content = $this->createContent();

        try {
            $service->applyTransition($content, 'publish_from_draft');
            self::fail('Expected TransitionBlockedException');
        } catch (TransitionBlockedException $exception) {
            self::assertSame('publish_from_draft', $exception->getTransitionName());
            self::assertSame('draft', $exception->getCurrentPlace());

            $violations = $exception->getViolations();
            self::assertCount(2, $violations);
            self::assertSame('description', $violations[0]['property_path']);
            self::assertSame('This value should not be blank.', $violations[0]['message']);
            self::assertSame('website', $violations[1]['property_path']);

            $reasons = $exception->getBlockerReasons();
            self::assertCount(2, $reasons);
            self::assertStringContainsString('description', $reasons[0]);
        }
    }

    public function testGenuinelyUnavailableTransitionStillThrowsInvalidTransition(): void
    {
        // No guard registered, but `archive_from_published` requires the
        // content to be in 'published' first. Apply from draft → expect
        // InvalidTransitionException, not TransitionBlockedException.
        $service = new ContentWorkflowService($this->createWorkflowRegistry(new EventDispatcher()));
        $content = $this->createContent();

        $this->expectException(\PsychedCms\Workflow\Exception\InvalidTransitionException::class);
        $service->applyTransition($content, 'archive_from_published');
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

    private function createWorkflowRegistry(EventDispatcher $dispatcher): Registry
    {
        $builder = new DefinitionBuilder();
        $builder
            ->addPlaces(['draft', 'review', 'scheduled', 'published', 'archived'])
            ->addTransition(new Transition('submit_for_review', 'draft', 'review'))
            ->addTransition(new Transition('publish_from_draft', 'draft', 'published'))
            ->addTransition(new Transition('archive_from_published', 'published', 'archived'))
            ->setInitialPlaces(['draft']);

        $definition = $builder->build();
        $markingStore = new MethodMarkingStore(true, 'status');
        $workflow = new StateMachine($definition, $markingStore, $dispatcher, 'content_publishing');

        $registry = new Registry();
        $registry->addWorkflow($workflow, new InstanceOfSupportStrategy(PublicationWorkflowAwareInterface::class));

        return $registry;
    }
}
