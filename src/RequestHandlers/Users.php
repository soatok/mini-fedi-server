<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\RequestHandlers;

use FediE2EE\PKD\Crypto\Exceptions\NotImplementedException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\FediServerConfig;
use Soatok\MiniFedi\Traits\ReqTrait;

/**
 * /users/{username}
 */
class Users implements RequestHandlerInterface
{
    use ReqTrait;

    public function profile(ServerRequestInterface $request): ResponseInterface
    {
        return $this->twig(
            'user.twig',
            ['username' => $request->getAttribute('username')]
        );
    }

    public function userInfo(ServerRequestInterface $request): ResponseInterface
    {
        $config = FediServerConfig::instance();
        $username = $request->getAttribute('username');
        try {
            $info = $this->table('Actors')->getActorInfo(
                $request->getAttribute('username')
            );
        } catch (TableException $e) {
            return $this->error('Actor not found', 404);
        }
        $profile = 'http://' . $config->vars()->hostname . '/users/' . urlencode($username);
        return $this->json([
            '@context' => ["https://www.w3.org/ns/activitystreams", "https://w3id.org/security/v1"],
            'id' => $profile,
            'type' => 'Person',
            'inbox' => $profile . '/inbox',
            'outbox' => $profile . '/outbox',
            'preferredUsername' => $info->preferredUsername,
            'name' => $info->username,
        ])->withHeader('Content-Type', 'application/activity+json');
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $username = $request->getAttribute('username');
        if (!$username) {
            return $this->error('unknown user', 404);
        }
        if ($request->hasHeader('Accept')) {
            $accept = strtolower($request->getHeader('Accept')[0]);
            return match ($accept) {
                'application/activity+json' => $this->userInfo($request),
                default => $this->profile($request),
            };
        }
        return $this->profile($request);
    }
}
