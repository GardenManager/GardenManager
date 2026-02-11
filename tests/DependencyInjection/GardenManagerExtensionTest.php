<?php

declare(strict_types=1);

namespace GardenManager\Tests\DependencyInjection;

use GardenManager\Shared\Infrastructure\DependencyInjection\GardenManagerExtension;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class GardenManagerExtensionTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $originalServer = [];

    /** @var array<string, mixed> */
    private array $originalEnv = [];

    protected function setUp(): void
    {
        $this->originalServer = $_SERVER;
        $this->originalEnv = $_ENV;

        // Clean any GM_ vars from previous tests
        foreach (array_keys($_SERVER) as $key) {
            if (str_starts_with((string) $key, 'GM_')) {
                unset($_SERVER[$key]);
            }
        }
        foreach (array_keys($_ENV) as $key) {
            if (str_starts_with((string) $key, 'GM_')) {
                unset($_ENV[$key]);
            }
        }
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        $_ENV = $this->originalEnv;
    }

    #[Test]
    public function setsDefaultParametersWhenNoConfigProvided(): void
    {
        $container = $this->loadExtension();

        self::assertTrue($container->getParameter('gm.require_email_verification'));
        self::assertSame('', $container->getParameter('gm.oidc.client_id'));
        self::assertSame('', $container->getParameter('gm.oidc.client_secret'));
        self::assertSame('', $container->getParameter('gm.oidc.issuer_url'));
    }

    #[Test]
    public function envVarOverridesDefault(): void
    {
        $_SERVER['GM_REQUIRE_EMAIL_VERIFICATION'] = 'false';

        $container = $this->loadExtension();

        self::assertFalse($container->getParameter('gm.require_email_verification'));
    }

    #[Test]
    public function envVarOverridesYamlConfig(): void
    {
        $_SERVER['GM_REQUIRE_EMAIL_VERIFICATION'] = 'false';

        $container = $this->loadExtension(['require_email_verification' => true]);

        self::assertFalse($container->getParameter('gm.require_email_verification'));
    }

    #[Test]
    public function oidcParamsDefaultToEmptyStrings(): void
    {
        $container = $this->loadExtension();

        self::assertSame('', $container->getParameter('gm.oidc.client_id'));
        self::assertSame('', $container->getParameter('gm.oidc.client_secret'));
        self::assertSame('', $container->getParameter('gm.oidc.issuer_url'));
    }

    #[Test]
    public function oidcEnvVarsSetParameters(): void
    {
        $_SERVER['GM_OIDC__CLIENT_ID'] = 'env-client';
        $_SERVER['GM_OIDC__CLIENT_SECRET'] = 'env-secret';
        $_SERVER['GM_OIDC__ISSUER_URL'] = 'https://issuer.example.com';

        $container = $this->loadExtension();

        self::assertSame('env-client', $container->getParameter('gm.oidc.client_id'));
        self::assertSame('env-secret', $container->getParameter('gm.oidc.client_secret'));
        self::assertSame('https://issuer.example.com', $container->getParameter('gm.oidc.issuer_url'));
    }

    #[Test]
    public function extensionAliasIsGm(): void
    {
        $extension = new GardenManagerExtension();

        self::assertSame('gm', $extension->getAlias());
    }

    /**
     * @param array<string, mixed> $config
     */
    private function loadExtension(array $config = []): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new GardenManagerExtension();
        $extension->load([$config], $container);

        return $container;
    }
}
