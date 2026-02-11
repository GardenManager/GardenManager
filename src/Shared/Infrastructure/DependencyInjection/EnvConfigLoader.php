<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\DependencyInjection;

use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\BooleanNode;
use Symfony\Component\Config\Definition\FloatNode;
use Symfony\Component\Config\Definition\IntegerNode;
use Symfony\Component\Config\Definition\NodeInterface;

final class EnvConfigLoader
{
    private const string NESTING_SEPARATOR = '__';

    /**
     * Reads environment variables matching the given prefix and maps them
     * to a configuration array based on the provided tree structure.
     *
     * @return array<string, mixed>
     */
    public function fromEnvironment(string $prefix, NodeInterface $tree): array
    {
        if (!$tree instanceof ArrayNode) {
            return [];
        }

        $environmentVariableMap = $this->buildExpectedVarMap($prefix, $tree);
        $config = [];

        foreach ($environmentVariableMap as $envVar => ['path' => $configPath, 'node' => $nodeType]) {
            $value = $_SERVER[$envVar] ?? $_ENV[$envVar] ?? null;

            if (!\is_string($value)) {
                continue;
            }

            $value = $this->coerceToNodeType($value, $nodeType);
            $this->setValueAtPath($config, $configPath, $value);
        }

        return $config;
    }

    /**
     * Returns a map of expected environment variable names to their tree paths and node types.
     *
     * @return array<string, array{path: list<string>, node: NodeInterface}>
     */
    public function buildExpectedVarMap(string $prefix, NodeInterface $tree): array
    {
        return $this->collectEnvironmentVariableMappings($prefix, $tree);
    }

    /**
     * @param list<string> $parentPath
     *
     * @return array<string, array{path: list<string>, node: NodeInterface}>
     */
    private function collectEnvironmentVariableMappings(string $prefix, NodeInterface $node, array $parentPath = []): array
    {
        if (!$node instanceof ArrayNode) {
            return [];
        }

        $mappings = [];

        foreach ($node->getChildren() as $childNode) {
            $configPath = [...$parentPath, $childNode->getName()];

            if ($childNode instanceof ArrayNode) {
                $mappings = array_merge($mappings, $this->collectEnvironmentVariableMappings($prefix, $childNode, $configPath));
            } else {
                $environmentVariableName = $this->buildEnvironmentVariableName($prefix, $configPath);
                $mappings[$environmentVariableName] = ['path' => $configPath, 'node' => $childNode];
            }
        }

        return $mappings;
    }

    /**
     * @param list<string> $configPath
     */
    private function buildEnvironmentVariableName(string $prefix, array $configPath): string
    {
        $uppercasedSegments = array_map(
            strtoupper(...),
            $configPath,
        );

        return $prefix . '_' . implode(self::NESTING_SEPARATOR, $uppercasedSegments);
    }

    private function coerceToNodeType(string $value, NodeInterface $node): string|bool|int|float
    {
        return match (true) {
            $node instanceof BooleanNode => $this->coerceToBoolean($value),
            $node instanceof IntegerNode => (int) $value,
            $node instanceof FloatNode => (float) $value,
            default => $value,
        };
    }

    private function coerceToBoolean(string $value): bool
    {
        return match (strtolower($value)) {
            'true', '1', 'yes', 'on' => true,
            default => false,
        };
    }

    /**
     * @param array<string, mixed> $config
     * @param list<string> $path
     */
    private function setValueAtPath(array &$config, array $path, mixed $value): void
    {
        $pointer = &$config;

        foreach ($path as $key) {
            if (!isset($pointer[$key])) {
                $pointer[$key] = [];
            }

            $pointer = &$pointer[$key];
        }

        $pointer = $value;
    }
}
