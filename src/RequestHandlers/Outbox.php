<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\RequestHandlers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Soatok\MiniFedi\Tables\OutboxTable;
use Soatok\MiniFedi\Traits\ActivityPubTrait;
use Soatok\MiniFedi\Traits\ReqTrait;

/**
 * /users/{username}/Outbox
 */
class Outbox implements RequestHandlerInterface
{
    use ActivityPubTrait;
    use ReqTrait;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (strtolower($request->getMethod()) === 'post') {
            return $this->handlePostRequest($request, new OutboxTable());
        }
        return $this->handleGetRequest($request);
    }
}
