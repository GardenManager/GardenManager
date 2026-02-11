<?php

declare(strict_types=1);

namespace GardenManager;

use GardenManager\DependencyInjection\Compiler\OidcProviderLoggerPass;
use GardenManager\Shared\Infrastructure\DependencyInjection\GardenManagerExtension;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->registerExtension(new GardenManagerExtension());
        $container->loadFromExtension('gm');
        $container->addCompilerPass(new OidcProviderLoggerPass());
    }
}
