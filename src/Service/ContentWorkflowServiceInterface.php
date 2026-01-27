<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Service;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

interface ContentWorkflowServiceInterface
{
    /**
     * Get the current workflow state for a content entity.
     *
     * @return array{
     *     place: string,
     *     available_transitions: list<string>
     * }
     */
    public function getWorkflowState(PublicationWorkflowAwareInterface $content): array;

    /**
     * Apply a transition to a content entity.
     */
    public function applyTransition(PublicationWorkflowAwareInterface $content, string $transitionName): void;

    /**
     * Check if a transition can be applied to a content entity.
     */
    public function canApplyTransition(PublicationWorkflowAwareInterface $content, string $transitionName): bool;
}
