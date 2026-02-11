<?php

namespace GardenManager\Tests\DependencyInjection;

use GardenManager\Shared\Infrastructure\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    /**
     * @param array<string, mixed> ...$configs
     *
     * @return array<string, mixed>
     */
    private function processConfig(array ...$configs): array
    {
        return new Processor()->processConfiguration(new Configuration(), $configs);
    }

    #[Test]
    public function defaultConfigHasExpectedValues(): void
    {
        $config = $this->processConfig([]);

        self::assertTrue($config['require_email_verification']);
        self::assertSame('', $config['oidc']['client_id']);
        self::assertSame('', $config['oidc']['client_secret']);
        self::assertSame('', $config['oidc']['issuer_url']);
    }

    #[Test]
    public function emailVerificationCanBeDisabled(): void
    {
        $config = $this->processConfig(['require_email_verification' => false]);

        self::assertFalse($config['require_email_verification']);
    }

    #[Test]
    public function oidcValuesCanBeSet(): void
    {
        $config = $this->processConfig([
            'oidc' => [
                'client_id' => 'my-id',
                'client_secret' => 'my-secret',
                'issuer_url' => 'https://provider.example.com',
            ],
        ]);

        self::assertSame('my-id', $config['oidc']['client_id']);
        self::assertSame('my-secret', $config['oidc']['client_secret']);
        self::assertSame('https://provider.example.com', $config['oidc']['issuer_url']);
    }

    #[Test]
    public function laterConfigOverridesEarlier(): void
    {
        $config = $this->processConfig(
            ['require_email_verification' => true],
            ['require_email_verification' => false],
        );

        self::assertFalse($config['require_email_verification']);
    }
}
