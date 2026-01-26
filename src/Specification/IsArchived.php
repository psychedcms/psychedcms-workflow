<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Specification;

use PsychedCms\Core\Domain\Specification\AbstractSpecification;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

final class IsArchived extends AbstractSpecification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        if (!$candidate instanceof PublicationWorkflowAwareInterface) {
            return false;
        }

        return $candidate->getStatus() === 'archived';
    }
}
