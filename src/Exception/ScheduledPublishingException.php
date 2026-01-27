<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\Exception;

/**
 * Thrown when auto-publishing of scheduled content fails.
 */
final class ScheduledPublishingException extends \RuntimeException implements WorkflowExceptionInterface
{
    public function __construct(
        private readonly string $contentClass,
        private readonly int|string $contentId,
        string $reason,
        ?\Throwable $previous = null,
    ) {
        $message = sprintf(
            'Failed to auto-publish scheduled content %s#%s: %s',
            $contentClass,
            $contentId,
            $reason
        );

        parent::__construct($message, 0, $previous);
    }

    public function getContentClass(): string
    {
        return $this->contentClass;
    }

    public function getContentId(): int|string
    {
        return $this->contentId;
    }
}
