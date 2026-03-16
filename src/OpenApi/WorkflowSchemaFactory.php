<?php

declare(strict_types=1);

namespace PsychedCms\Workflow\OpenApi;

use ApiPlatform\JsonSchema\Schema;
use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Operation;
use PsychedCms\Workflow\Content\PublicationWorkflowAwareInterface;
use Symfony\Component\Workflow\Registry;

final class WorkflowSchemaFactory implements SchemaFactoryInterface
{
    private ?array $transitionMap = null;

    public function __construct(
        private readonly SchemaFactoryInterface $decorated,
        private readonly Registry $workflowRegistry,
    ) {
    }

    public function buildSchema(
        string $className,
        string $format = 'json',
        string $type = Schema::TYPE_OUTPUT,
        ?Operation $operation = null,
        ?Schema $schema = null,
        ?array $serializerContext = null,
        bool $forceCollection = false,
    ): Schema {
        $schema = $this->decorated->buildSchema(
            $className, $format, $type, $operation, $schema, $serializerContext, $forceCollection
        );

        if (!class_exists($className) || !is_a($className, PublicationWorkflowAwareInterface::class, true)) {
            return $schema;
        }

        $definitions = $schema->getDefinitions();
        $shortClassName = (new \ReflectionClass($className))->getShortName();

        foreach ($definitions as $definitionName => $definition) {
            if (!str_starts_with($definitionName, $shortClassName)) {
                continue;
            }

            if (isset($definition['x-psychedcms'])) {
                $xp = $definition['x-psychedcms'];
                $xp['workflow'] = $this->getTransitionMap($className);
                $definition['x-psychedcms'] = $xp;
                $definitions[$definitionName] = $definition;
            }
        }

        return $schema;
    }

    /**
     * Build a map of place => available transition names from the workflow definition.
     *
     * @return array<string, list<string>>
     */
    private function getTransitionMap(string $className): array
    {
        if ($this->transitionMap !== null) {
            return $this->transitionMap;
        }

        try {
            $workflow = $this->workflowRegistry->get(new $className(), 'content_publishing');
        } catch (\Throwable) {
            return $this->transitionMap = [];
        }

        $definition = $workflow->getDefinition();
        $map = [];

        foreach ($definition->getPlaces() as $place) {
            $map[$place] = [];
        }

        foreach ($definition->getTransitions() as $transition) {
            foreach ($transition->getFroms() as $from) {
                $map[$from][] = $transition->getName();
            }
        }

        return $this->transitionMap = $map;
    }
}
