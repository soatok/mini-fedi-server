<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\RequestHandlers;

use FediE2EE\PKD\Crypto\Exceptions\NetworkException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use ParagonIE\Certainty\Exception\CertaintyException;
use ParagonIE\Certainty\RemoteFetch;
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface;
use Soatok\MiniFedi\FediServerConfig;
use Soatok\MiniFedi\Traits\ReqTrait;
use SodiumException;

class WebFinger implements RequestHandlerInterface
{
    use ReqTrait;

    public bool $allowRemoteLookups;
    protected FediServerConfig $config;

    public function __construct(?FediServerConfig $config = null, bool $allowRemoteLookups = true)
    {
        if (is_null($config)) {
            $config = new FediServerConfig();
        }
        $this->config = $config;
        $this->allowRemoteLookups = $allowRemoteLookups;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $vars = $this->config->vars();
        $params = $request->getQueryParams();
        if (!array_key_exists("resource", $params)) {
            return $this->error('missing resource parameter', 400);
        }
        $resource = $params["resource"];
        $matches = [];
        if (!preg_match('#^acct:([^@]+)@(.+)$#', $resource, $matches)) {
            return $this->error('invalid resource format', 400);
        }
        $user = $matches[1];
        $domain = $matches[2];

        if (!hash_equals($vars->hostname, $domain)) {
            // Remote webfinger query
            if ($this->allowRemoteLookups) {
                try {
                    return $this->remoteLookup($user, $domain);
                } catch (CertaintyException|NetworkException|GuzzleException $e) {
                    return $this->error($e->getMessage(), $e->getCode());
                }
            }
            return $this->error('domain not local:' . $domain, 404);
        }

        $profile = 'http://' . $vars->hostname . '/users/' . urlencode($user);
        return $this->json([
            'subject' => $resource,
            'aliases' => [$profile],
            'links' => [
                [
                    'rel' => 'self',
                    'type' => 'application/activity+json',
                    'href' => $profile
                ]
            ]
        ])->withHeader('Content-Type', 'application/jrd+json');
    }

    /**
     * @throws CertaintyException
     * @throws GuzzleException
     * @throws NetworkException
     * @throws SodiumException
     */
    protected function remoteLookup(string $domain, string $user): ResponseInterface
    {
        $url = "https://{$domain}/.well-known/webfinger?resource=acct:{$user}";
        $http = new Client([
            'verify' => (new RemoteFetch(MINIFEDI_BASE_DIR . '/tmp'))
                ->getLatestBundle()
                ->getFilePath()
        ]);
        $response = $http->get($url);
        if ($response->getStatusCode() !== 200) {
            throw new NetworkException('Could not connect to ' . $domain);
        }

        // Make sure it returns valid JSON:
        $jrd = json_decode($response->getBody()->getContents(), true);
        if (!is_array($jrd)) {
            throw new NetworkException('Invalid JSON: ' . json_last_error_msg());
        }

        // Return the response:
        $response->getBody()->rewind();
        return $response;
    }
}
