<?php

declare(strict_types=1);

namespace GardenManager\Shared\Infrastructure\DependencyInjection;

use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

final class GardenManagerExtension extends Extension implements PrependExtensionInterface
{
    #[Override]
    public function prepend(ContainerBuilder $container): void
    {
        $this->setParameters($container->getExtensionConfig('gm'), $container);
    }

    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->setParameters($configs, $container);
    }

    #[Override]
    public function getAlias(): string
    {
        return 'gm';
    }

    /**
     * @param array<array<string, mixed>> $configs
     */
    private function setParameters(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $tree = $configuration->getConfigTreeBuilder()->buildTree();

        $configs[] = new EnvConfigLoader()->fromEnvironment('GM', $tree);

        $resolved = $this->processConfiguration($configuration, $configs);

        $this->flattenToParameters($container, $resolved, 'gm');
    }

    /**
     * @param array<string, mixed> $config
     */
    private function flattenToParameters(ContainerBuilder $container, array $config, string $prefix): void
    {
        foreach ($config as $key => $value) {
            $paramName = $prefix . '.' . $key;

            if (\is_array($value)) {
                $this->flattenToParameters($container, $value, $paramName);
            } else {
                $container->setParameter($paramName, $value);
            }
        }
    }
}
