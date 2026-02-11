<?php

namespace GardenManager\Tests\DependencyInjection;

use GardenManager\Shared\Infrastructure\DependencyInjection\Configuration;
use GardenManager\Shared\Infrastructure\DependencyInjection\EnvConfigLoader;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnvConfigLoaderTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $originalServer = [];

    /** @var array<string, mixed> */
    private array $originalEnv = [];

    protected function setUp(): void
    {
        $this->originalServer = $_SERVER;
        $this->originalEnv = $_ENV;

        // Clean any GM_ vars leaked from .env bootstrap
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

    private function buildTree(): \Symfony\Component\Config\Definition\NodeInterface
    {
        return (new Configuration())->getConfigTreeBuilder()->buildTree();
    }

    private function createLoader(): EnvConfigLoader
    {
        return new EnvConfigLoader();
    }

    #[Test]
    public function returnsEmptyArrayWhenNoEnvVarsSet(): void
    {
        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame([], $result);
    }

    #[Test]
    public function mapsBooleanEnvVarFalse(): void
    {
        $_SERVER['GM_REQUIRE_EMAIL_VERIFICATION'] = 'false';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame(['require_email_verification' => false], $result);
    }

    #[Test]
    public function mapsBooleanEnvVarTrue(): void
    {
        $_SERVER['GM_REQUIRE_EMAIL_VERIFICATION'] = 'true';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame(['require_email_verification' => true], $result);
    }

    #[Test]
    public function coercesBooleanFromNumericOne(): void
    {
        $_SERVER['GM_REQUIRE_EMAIL_VERIFICATION'] = '1';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame(['require_email_verification' => true], $result);
    }

    #[Test]
    public function coercesBooleanFromNumericZero(): void
    {
        $_SERVER['GM_REQUIRE_EMAIL_VERIFICATION'] = '0';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame(['require_email_verification' => false], $result);
    }

    #[Test]
    public function mapsNestedEnvVar(): void
    {
        $_SERVER['GM_OIDC__CLIENT_ID'] = 'my-client-id';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame(['oidc' => ['client_id' => 'my-client-id']], $result);
    }

    #[Test]
    public function mapsMultipleNestedEnvVars(): void
    {
        $_SERVER['GM_OIDC__CLIENT_ID'] = 'cid';
        $_SERVER['GM_OIDC__CLIENT_SECRET'] = 'secret';
        $_SERVER['GM_OIDC__ISSUER_URL'] = 'https://example.com';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame([
            'oidc' => [
                'client_id' => 'cid',
                'client_secret' => 'secret',
                'issuer_url' => 'https://example.com',
            ],
        ], $result);
    }

    #[Test]
    public function readsFromEnvFallback(): void
    {
        $_ENV['GM_OIDC__CLIENT_ID'] = 'from-env';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame(['oidc' => ['client_id' => 'from-env']], $result);
    }

    #[Test]
    public function serverTakesPrecedenceOverEnv(): void
    {
        $_SERVER['GM_OIDC__CLIENT_ID'] = 'from-server';
        $_ENV['GM_OIDC__CLIENT_ID'] = 'from-env';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame(['oidc' => ['client_id' => 'from-server']], $result);
    }

    #[Test]
    public function buildsExpectedVarMap(): void
    {
        $map = $this->createLoader()->buildExpectedVarMap('GM', $this->buildTree());

        self::assertArrayHasKey('GM_REQUIRE_EMAIL_VERIFICATION', $map);
        self::assertArrayHasKey('GM_OIDC__CLIENT_ID', $map);
        self::assertArrayHasKey('GM_OIDC__CLIENT_SECRET', $map);
        self::assertArrayHasKey('GM_OIDC__ISSUER_URL', $map);

        self::assertSame(['require_email_verification'], $map['GM_REQUIRE_EMAIL_VERIFICATION']['path']);
        self::assertSame(['oidc', 'client_id'], $map['GM_OIDC__CLIENT_ID']['path']);
    }

    #[Test]
    public function ignoresUnrelatedEnvVars(): void
    {
        $_SERVER['GM_NONEXISTENT_VAR'] = 'should-be-ignored';

        $result = $this->createLoader()->fromEnvironment('GM', $this->buildTree());

        self::assertSame([], $result);
    }
}
