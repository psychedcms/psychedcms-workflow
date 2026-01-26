<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Specification;

use PsychedCms\Core\Domain\Specification\AbstractSpecification;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

final class IsPublicationWorkflowAware extends AbstractSpecification
{
    public function isSatisfiedBy(object $candidate): bool
    {
        return $candidate instanceof PublicationWorkflowAwareInterface;
    }

    /**
     * Check if a class implements PublicationWorkflowAwareInterface.
     */
    public function isSatisfiedByClass(string $className): bool
    {
        return is_a($className, PublicationWorkflowAwareInterface::class, true);
    }
}
