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

        if ($violations->count() > 0) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage());
            }

            $event->addTransitionBlocker(
                new \Symfony\Component\Workflow\TransitionBlocker(
                    implode('; ', $messages),
                    'validation_failed'
                )
            );
        }
    }
}
