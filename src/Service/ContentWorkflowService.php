<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Service;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use PsychedCms\Workflow\Exception\InvalidTransitionException;
use PsychedCms\Workflow\Exception\TransitionBlockedException;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Registry;

final class ContentWorkflowService implements ContentWorkflowServiceInterface
{
    private const WORKFLOW_NAME = 'content_publishing';

    public function __construct(
        private readonly Registry $workflowRegistry,
    ) {
    }

    /**
     * Get the current workflow state for a content entity.
     *
     * @return array{
     *     place: string,
     *     available_transitions: list<string>
     * }
     */
    public function getWorkflowState(PublicationWorkflowAwareInterface $content): array
    {
        $workflow = $this->workflowRegistry->get($content, self::WORKFLOW_NAME);
        $marking = $workflow->getMarking($content);

        $places = array_keys($marking->getPlaces());
        $currentPlace = $places[0] ?? 'draft';

        $availableTransitions = array_map(
            static fn ($transition) => $transition->getName(),
            $workflow->getEnabledTransitions($content)
        );

        return [
            'place' => $currentPlace,
            'available_transitions' => $availableTransitions,
        ];
    }

    /**
     * Apply a transition to a content entity.
     *
     * @throws InvalidTransitionException When the transition is not available
     * @throws TransitionBlockedException When a guard blocks the transition
     */
    public function applyTransition(PublicationWorkflowAwareInterface $content, string $transitionName, array $context = []): void
    {
        $workflow = $this->workflowRegistry->get($content, self::WORKFLOW_NAME);
        $currentPlace = $content->getStatus();

        $availableTransitions = array_map(
            static fn ($transition) => $transition->getName(),
            $workflow->getEnabledTransitions($content)
        );

        if (!$workflow->can($content, $transitionName)) {
            throw new InvalidTransitionException(
                $transitionName,
                $currentPlace,
                $availableTransitions
            );
        }

        try {
            $workflow->apply($content, $transitionName);
        } catch (NotEnabledTransitionException $e) {
            $blockerReasons = [];
            foreach ($e->getTransitionBlockerList() as $blocker) {
                $blockerReasons[] = $blocker->getMessage();
            }

            throw new TransitionBlockedException(
                $transitionName,
                $currentPlace,
                $blockerReasons,
                $e
            );
        }
    }

    /**
     * Check if a transition can be applied to a content entity.
     */
    public function canApplyTransition(PublicationWorkflowAwareInterface $content, string $transitionName): bool
    {
        $workflow = $this->workflowRegistry->get($content, self::WORKFLOW_NAME);

        return $workflow->can($content, $transitionName);
    }
}
