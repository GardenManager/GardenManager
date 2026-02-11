<?php

namespace GardenManager\Shared\Infrastructure\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    #[\Override]
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('gm');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('require_email_verification')
                    ->defaultTrue()
                    ->info('Whether new users must verify their email address before logging in. Env: GM_REQUIRE_EMAIL_VERIFICATION')
                ->end()
                ->arrayNode('oidc')
                    ->addDefaultsIfNotSet()
                    ->info('OpenID Connect provider settings. Leave client_id empty to disable OIDC.')
                    ->children()
                        ->scalarNode('client_id')
                            ->defaultValue('')
                            ->info('OIDC client ID. Env: GM_OIDC__CLIENT_ID')
                        ->end()
                        ->scalarNode('client_secret')
                            ->defaultValue('')
                            ->info('OIDC client secret. Env: GM_OIDC__CLIENT_SECRET')
                        ->end()
                        ->scalarNode('issuer_url')
                            ->defaultValue('')
                            ->info('OIDC issuer/discovery URL. Env: GM_OIDC__ISSUER_URL')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
