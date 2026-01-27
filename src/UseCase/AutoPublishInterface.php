<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\UseCase;

use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;

interface AutoPublishInterface
{
    public function execute(PublicationWorkflowAwareInterface $content): void;
}
