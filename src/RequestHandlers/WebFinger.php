<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\RequestHandlers;

use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface,
};
use Psr\Http\Server\RequestHandlerInterface;
use Soatok\MiniFedi\FediServerConfig;
use Soatok\MiniFedi\Traits\ReqTrait;

class WebFinger implements RequestHandlerInterface
{
    use ReqTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $config = FediServerConfig::instance();
        $vars = $config->vars();
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
            // Remote webfigner query
        }

    }
}
