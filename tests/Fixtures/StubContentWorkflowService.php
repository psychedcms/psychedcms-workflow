<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Tests\Fixtures;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

class StubContentWorkflowService
{
    /** @var array<array{content: PublicationWorkflowAwareInterface, transition: string}> */
    public array $appliedTransitions = [];

    public function applyTransition(PublicationWorkflowAwareInterface $content, string $transitionName): void
    {
        $this->appliedTransitions[] = ['content' => $content, 'transition' => $transitionName];
    }

    public function getWorkflowState(PublicationWorkflowAwareInterface $content): array
    {
        return ['place' => 'draft', 'available_transitions' => []];
    }

    public function canApplyTransition(PublicationWorkflowAwareInterface $content, string $transitionName): bool
    {
        return true;
    }
}
