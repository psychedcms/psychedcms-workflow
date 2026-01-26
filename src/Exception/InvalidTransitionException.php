<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Exception;

/**
 * Thrown when attempting an invalid or unavailable transition.
 *
 * HTTP Status: 400 Bad Request
 */
final class InvalidTransitionException extends \InvalidArgumentException implements WorkflowExceptionInterface
{
    public function __construct(
        private readonly string $transitionName,
        private readonly string $currentPlace,
        private readonly array $availableTransitions = [],
        ?\Throwable $previous = null,
    ) {
        $message = sprintf(
            'Transition "%s" is not available from place "%s".',
            $transitionName,
            $currentPlace
        );

        if ($availableTransitions !== []) {
            $message .= sprintf(
                ' Available transitions: %s.',
                implode(', ', $availableTransitions)
            );
        }

        parent::__construct($message, 400, $previous);
    }

    public function getTransitionName(): string
    {
        return $this->transitionName;
    }

    public function getCurrentPlace(): string
    {
        return $this->currentPlace;
    }

    public function getAvailableTransitions(): array
    {
        return $this->availableTransitions;
    }
}
