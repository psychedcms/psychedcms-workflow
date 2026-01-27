<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\ApiPlatform;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use PsychedCms\Workflow\Action\ApproveAction;
use PsychedCms\Workflow\Action\ArchiveAction;
use PsychedCms\Workflow\Action\GetWorkflowStateAction;
use PsychedCms\Workflow\Action\PublishAction;
use PsychedCms\Workflow\Action\RequestChangesAction;
use PsychedCms\Workflow\Action\RestoreAction;
use PsychedCms\Workflow\Action\ScheduleAction;
use PsychedCms\Workflow\Action\SubmitForReviewAction;
use PsychedCms\Workflow\Action\UnpublishAction;
use PsychedCms\Workflow\Action\UnscheduleAction;
use PsychedCms\Workflow\Specification\IsPublicationWorkflowAware;

final readonly class WorkflowOperationsResourceMetadataCollectionFactory implements ResourceMetadataCollectionFactoryInterface
{
    private const WORKFLOW_OPERATIONS = [
        'workflow_state' => [
            'controller' => GetWorkflowStateAction::class,
            'method' => 'GET',
            'path' => '/workflow-state',
            'openapi' => [
                'summary' => 'Get workflow state',
                'description' => 'Returns the current workflow place and available transitions.',
            ],
        ],
        'submit_for_review' => [
            'controller' => SubmitForReviewAction::class,
            'method' => 'POST',
            'path' => '/submit-for-review',
            'openapi' => [
                'summary' => 'Submit for review',
                'description' => 'Submits the content for review.',
            ],
        ],
        'request_changes' => [
            'controller' => RequestChangesAction::class,
            'method' => 'POST',
            'path' => '/request-changes',
            'openapi' => [
                'summary' => 'Request changes',
                'description' => 'Requests changes and returns content to draft.',
            ],
        ],
        'approve' => [
            'controller' => ApproveAction::class,
            'method' => 'POST',
            'path' => '/approve',
            'openapi' => [
                'summary' => 'Approve',
                'description' => 'Approves and publishes the content.',
            ],
        ],
        'publish' => [
            'controller' => PublishAction::class,
            'method' => 'POST',
            'path' => '/publish',
            'openapi' => [
                'summary' => 'Publish',
                'description' => 'Directly publishes the content.',
            ],
        ],
        'schedule' => [
            'controller' => ScheduleAction::class,
            'method' => 'POST',
            'path' => '/schedule',
            'openapi' => [
                'summary' => 'Schedule',
                'description' => 'Schedules the content for future publication.',
            ],
        ],
        'unschedule' => [
            'controller' => UnscheduleAction::class,
            'method' => 'POST',
            'path' => '/unschedule',
            'openapi' => [
                'summary' => 'Unschedule',
                'description' => 'Cancels scheduled publication and returns content to draft.',
            ],
        ],
        'unpublish' => [
            'controller' => UnpublishAction::class,
            'method' => 'POST',
            'path' => '/unpublish',
            'openapi' => [
                'summary' => 'Unpublish',
                'description' => 'Unpublishes the content and returns it to draft.',
            ],
        ],
        'archive' => [
            'controller' => ArchiveAction::class,
            'method' => 'POST',
            'path' => '/archive',
            'openapi' => [
                'summary' => 'Archive',
                'description' => 'Archives the content.',
            ],
        ],
        'restore' => [
            'controller' => RestoreAction::class,
            'method' => 'POST',
            'path' => '/restore',
            'openapi' => [
                'summary' => 'Restore',
                'description' => 'Restores archived content to draft.',
            ],
        ],
    ];

    public function __construct(
        private ResourceMetadataCollectionFactoryInterface $decorated,
    ) {
    }

    public function create(string $resourceClass): ResourceMetadataCollection
    {
        $resourceMetadataCollection = $this->decorated->create($resourceClass);

        if (!(new IsPublicationWorkflowAware())->isSatisfiedByClass($resourceClass)) {
            return $resourceMetadataCollection;
        }

        $resources = [];
        foreach ($resourceMetadataCollection as $resource) {
            $resources[] = $this->addWorkflowOperations($resource, $resourceClass);
        }

        return new ResourceMetadataCollection($resourceClass, $resources);
    }

    private function addWorkflowOperations(ApiResource $resource, string $resourceClass): ApiResource
    {
        $operations = iterator_to_array($resource->getOperations() ?? []);
        $uriTemplate = $this->getBaseUriTemplate($resource, $resourceClass);
        $shortName = $resource->getShortName() ?? $this->getShortName($resourceClass);

        foreach (self::WORKFLOW_OPERATIONS as $operationName => $config) {
            $operationKey = sprintf('%s_%s', $this->getShortName($resourceClass), $operationName);

            if (isset($operations[$operationKey])) {
                continue;
            }

            $operationClass = $config['method'] === 'GET' ? Get::class : Post::class;

            $operations[$operationKey] = new $operationClass(
                uriTemplate: $uriTemplate . $config['path'],
                class: $resourceClass,
                shortName: $shortName,
                controller: $config['controller'],
                name: $operationKey,
                read: true,
                deserialize: false,
                validate: false,
                write: false,
                openapi: new \ApiPlatform\OpenApi\Model\Operation(
                    summary: $config['openapi']['summary'],
                    description: $config['openapi']['description'],
                ),
            );
        }

        return $resource->withOperations(new \ApiPlatform\Metadata\Operations($operations));
    }

    private function getBaseUriTemplate(ApiResource $resource, string $resourceClass): string
    {
        $shortName = $resource->getShortName() ?? $this->getShortName($resourceClass);

        return sprintf('/%s/{id}', strtolower($shortName) . 's');
    }

    private function getShortName(string $resourceClass): string
    {
        $parts = explode('\\', $resourceClass);

        return end($parts);
    }
}
