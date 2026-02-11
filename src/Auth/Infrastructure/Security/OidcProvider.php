<?php

namespace GardenManager\Auth\Infrastructure\Security;

use League\OAuth2\Client\Provider\GenericProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

final class OidcProvider extends GenericProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(array $options = [], array $collaborators = [])
    {
        $issuerUrl = $options['issuer_url'] ?? '';
        unset($options['issuer_url']);

        if ($issuerUrl !== '') {
            $discovery = $this->fetchOpenIdConfiguration($issuerUrl);

            $options['urlAuthorize'] = $discovery['authorization_endpoint'];
            $options['urlAccessToken'] = $discovery['token_endpoint'];
            $options['urlResourceOwnerDetails'] = $discovery['userinfo_endpoint'];
        } else {
            $options['urlAuthorize'] = $options['urlAuthorize'] ?? '';
            $options['urlAccessToken'] = $options['urlAccessToken'] ?? '';
            $options['urlResourceOwnerDetails'] = $options['urlResourceOwnerDetails'] ?? '';
        }

        parent::__construct($options, $collaborators);
    }

    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        $response->getBody()->rewind();
        $this->logger?->debug($response->getBody()->getContents());
    }

    /** @return array<string, mixed> */
    private function fetchOpenIdConfiguration(string $issuerUrl): array
    {
        $url = rtrim($issuerUrl, '/') . '/.well-known/openid-configuration';

        $response = file_get_contents($url);

        if ($response === false) {
            throw new \RuntimeException(\sprintf('Failed to fetch OpenID configuration from "%s".', $url));
        }

        $data = json_decode($response, true, 512, \JSON_THROW_ON_ERROR);

        foreach (['authorization_endpoint', 'token_endpoint', 'userinfo_endpoint'] as $key) {
            if (!isset($data[$key])) {
                throw new \RuntimeException(\sprintf('Missing "%s" in OpenID configuration.', $key));
            }
        }

        return $data;
    }
}
