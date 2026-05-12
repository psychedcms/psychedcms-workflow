<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\EventListener;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Workflow\Event\GuardEvent;

final class WorkflowGuardListener
{
    private const TRANSITION_VALIDATION_GROUPS = [
        'draft' => ['draft'],
        'review' => ['draft', 'review'],
        'published' => ['draft', 'published'],
        'scheduled' => ['draft', 'published'],
    ];

    public function __construct(
        private readonly ValidatorInterface $validator,
    ) {
    }

    public function onGuard(GuardEvent $event): void
    {
        $transition = $event->getTransition();
        if ($transition === null) {
            return;
        }

        $tos = $transition->getTos();
        $targetPlace = $tos[0] ?? null;

        if ($targetPlace === null) {
            return;
        }

        $groups = self::TRANSITION_VALIDATION_GROUPS[$targetPlace] ?? null;
        if ($groups === null) {
            return;
        }

        $subject = $event->getSubject();
        $violations = $this->validator->validate($subject, null, $groups);

        // One blocker per violation: the API layer renders them as a
        // ConstraintViolationList-shaped payload (one entry per field) instead
        // of swallowing every violation into a single "transition not available"
        // 500. Keeping the property path in the blocker `parameters` lets the
        // frontend bind each error to its own form field.
        foreach ($violations as $violation) {
            $event->addTransitionBlocker(
                new \Symfony\Component\Workflow\TransitionBlocker(
                    sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage()),
                    'validation_failed',
                    [
                        'property_path' => $violation->getPropertyPath(),
                        'message' => (string) $violation->getMessage(),
                    ],
                )
            );
        }
    }
}
