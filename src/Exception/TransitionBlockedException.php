<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Exception;

/**
 * Thrown when a workflow guard blocks a transition.
 *
 * HTTP Status: 422 Unprocessable Entity
 */
final class TransitionBlockedException extends \RuntimeException implements WorkflowExceptionInterface
{
    public function __construct(
        private readonly string $transitionName,
        private readonly string $currentPlace,
        private readonly array $blockerReasons = [],
        ?\Throwable $previous = null,
    ) {
        $message = sprintf(
            'Transition "%s" from "%s" is blocked.',
            $transitionName,
            $currentPlace
        );

        if ($blockerReasons !== []) {
            $message .= ' Reasons: ' . implode(', ', $blockerReasons);
        }

        parent::__construct($message, 422, $previous);
    }

    public function getTransitionName(): string
    {
        return $this->transitionName;
    }

    public function getCurrentPlace(): string
    {
        return $this->currentPlace;
    }

    public function getBlockerReasons(): array
    {
        return $this->blockerReasons;
    }
}
