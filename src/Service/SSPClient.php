<?php declare(strict_types=1);

namespace CreativeCommoners\CreateSSDemo\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

/**
 * A Guzzle wrapper targeting SilverStripe Platform API calls
 *
 * Mostly copied from https://github.com/silverstripeltd/silverstripe-demostacker/blob/master/src/SSPAPIBuilder.php
 */
class SSPClient
{
    /**
     * @var string
     */
    const API_DOMAIN = 'https://platform.silverstripe.com';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client = null)
    {
        if (!$client) {
            $client = new Client([
                'base_uri' => self::API_DOMAIN,
            ]);
        }
        $this->setClient($client);
    }

    /**
     * Request to instantiate a new demo instance
     *
     * @param string $name        The demo site's name, e.g. "Sally's Salon"
     * @param string $imageId     Docker image ID and version, e.g. "sally/salon:1.2"
     * @param string $stackName   The SSP stack name, e.g. "sallys-salon"
     * @param int $snapshotId     The content snapshot ID from SSP, e.g. 12345
     * @param string $environment The SSP environment name to build demos in
     * @return int                The demo ID from SSP (or 0 on failure)
     */
    public function instantiate(
        string $name,
        string $imageId,
        string $stackName,
        int $snapshotId,
        string $environment = 'prod'
    ): int {
        $response = $this->makeRequest('POST', $this->getEndpoint($stackName, $environment), [
            'name' => $name,
            'image' => $imageId,
            'snapshot_id' => $snapshotId,
        ]);

        $data = json_decode((string)$response->getBody(), true);
        if (!empty($data['data']['id'])) {
            return (int) $data['data']['id'];
        }
        return 0;
    }

    /**
     * Gets the status of a demo build from SilverStripe Platform, and will return the raw API result data
     *
     * @param string $stackName
     * @param int $demoId
     * @param string $environment
     * @return array
     */
    public function status(string $stackName, int $demoId, string $environment = 'prod'): array
    {
        $response = $this->makeRequest('GET', $this->getEndpoint($stackName, $environment, $demoId));

        return json_decode((string)$response->getBody(), true) ?: [];
    }

    /**
     * Ask SilverStripe Platform to destroy an existing demo site
     *
     * @param string $stackName
     * @param int $demoId
     * @param string $environment
     * @return bool
     */
    public function destroy(string $stackName, int $demoId, string $environment = 'prod'): bool
    {
        $result = $this->makeRequest('DELETE', $this->getEndpoint($stackName, $environment, $demoId));

        return $result->getStatusCode() === 204;
    }

    public function setClient(ClientInterface $client): SSPClient
    {
        $this->client = $client;
        return $this;
    }

    protected function getEndpoint(string $stackName, string $environment, int $demoId = null): string
    {
        $endpoint = "/naut/project/{$stackName}/environment/{$environment}/demo";
        if ($demoId) {
            $endpoint .= "/{$demoId}";
        }
        return $endpoint;
    }

    protected function makeRequest(string $method, string $endpoint, array $data = []): ResponseInterface
    {
        $user = getenv('SS_DEMO_AUTH_USER');
        $apiKey = getenv('SS_DEMO_AUTH_KEY');
        if (empty($user) || empty($apiKey)) {
            throw new InvalidArgumentException(
                'You have not defined one of either SS_DEMO_AUTH_USER or SS_DEMO_AUTH_KEY for SilverStripe'
                . ' Platform API access!'
            );
        }

        $requestOptions = [
            'auth' => [$user, $apiKey],
            'json' => $data,
            'headers' => [
                'X-Api-Version' => '2.0',
            ],
        ];

        return $this->client->request($method, $endpoint, $requestOptions);
    }
}
