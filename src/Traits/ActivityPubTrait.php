<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Traits;

use JsonException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Soatok\MiniFedi\Exceptions\ConfigException;
use Soatok\MiniFedi\Exceptions\InvalidRequestException;
use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\Tables\Actors;
use Soatok\MiniFedi\Tables\InboxTable;
use Soatok\MiniFedi\Tables\OutboxTable;
use SodiumException;

/**
 * @method ResponseInterface error(string $message, int $code)
 */
trait ActivityPubTrait
{
    /**
     * @throws JsonException
     * @throws ConfigException
     * @throws SodiumException
     */
    protected function handlePostRequest(
        ServerRequestInterface $request,
        InboxTable|OutboxTable $table
    ): ResponseInterface {
        // Fetch the actor
        $actors = new Actors();
        $username = $request->getAttribute('username');
        if (empty($username)) {
            return $this->error('No username provided');
        }
        $actor = $actors->getActorInfo($username);
        if (!$actor->hasPrimaryKey()) {
            // Did you forget to save()?s
            return $this->error('No primary key on this record???', 400);
        }
        // Handle the request:
        try {
            if ($table->accept($request, $actor)) {
                return $this->json(['message' => 'Accepted'], 202);
            }
            return $this->error('Could not save message', 500);
        } catch (TableException $ex) {
            return $this->error($ex->getMessage(), 404);
        } catch (InvalidRequestException $ex) {
            return $this->error($ex->getMessage(), 400);
        }
    }

    protected function handleGetRequest(ServerRequestInterface $request): ResponseInterface
    {
        /** @var array<string, string> $vars */
        $vars = $request->getAttribute('vars');
        $username = $vars['username'] ?? '';
        if (empty($username)) {
            return $this->error('No username provided', 400);
        }

        // For now, let's return an empty collection.
        // This is not part of the core task, but it should return a valid response.
        return $this->json([
            '@context' => 'https://www.w3.org/ns/activitystreams',
            'id' => "https://{$request->getUri()->getHost()}/users/{$username}/inbox",
            'type' => 'OrderedCollection',
            'totalItems' => 0,
            'orderedItems' => [],
        ]);
    }
}