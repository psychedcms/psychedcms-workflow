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
    /**
     * @param list<string>                                        $blockerReasons
     * @param list<array{property_path: string, message: string}> $violations
     */
    public function __construct(
        private readonly string $transitionName,
        private readonly string $currentPlace,
        private readonly array $blockerReasons = [],
        ?\Throwable $previous = null,
        private readonly array $violations = [],
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

    /** @return list<string> */
    public function getBlockerReasons(): array
    {
        return $this->blockerReasons;
    }

    /**
     * Structured per-field violations, when the upstream blockers carried
     * `property_path` + `message` in their parameters. Empty when the
     * blockers come from a generic guard (e.g. authorisation) that has no
     * field to point at.
     *
     * @return list<array{property_path: string, message: string}>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }
}
