<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\RequestHandlers;

use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Soatok\MiniFedi\Traits\ReqTrait;

/**
 * /users/{username}/inbox
 */
class Inbox implements RequestHandlerInterface
{
    use ReqTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new NotImplementedException('Not implemented');
    }
}
