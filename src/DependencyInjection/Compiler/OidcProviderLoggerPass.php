<?php

declare(strict_types=1);

namespace GardenManager\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class OidcProviderLoggerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('knpu.oauth2.provider.oidc')) {
            return;
        }

        $container->getDefinition('knpu.oauth2.provider.oidc')
            ->addMethodCall('setLogger', [new Reference('logger')]);
    }
}
