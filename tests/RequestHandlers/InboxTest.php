<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tests\RequestHandlers;

use FediE2EE\PKD\Crypto\HttpSignature;
use FediE2EE\PKD\Crypto\SecretKey;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Soatok\MiniFedi\Orchestration;
use Soatok\MiniFedi\RequestHandlers\Inbox;
use Soatok\MiniFedi\Tables\Actors;
use Soatok\MiniFedi\Tables\InboxTable;
use TypeError;

#[CoversClass(Inbox::class)]
class InboxTest extends TestCase
{
    public function testLocalActorInboxPost(): void
    {
        $orchestration = new Orchestration();
        $actorsTable = new Actors($orchestration->getDb());
        $handler = new Inbox();
        $signer = new HttpSignature();

        $orchestration->stash();
        $actor = $orchestration->createActor('phpunit-' . bin2hex(random_bytes(16)));
        $actor->summary = 'A dummy actor created for PHPUnit testing';
        $this->assertTrue($actorsTable->save($actor));
        $this->assertTrue($actor->hasPrimaryKey());

        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();
        $orchestration->createPublicKeyForActor($actor, $pk->toString());

        $uri = 'https://minifedi.localhost/users/' . urlencode($actor->username) . '/inbox';
        $body = json_encode(['@context' => 'https://www.w3.org/ns/activitystreams', 'type' => 'Follow']);
        $fp = fopen('php://temp', 'w+');
        fwrite($fp, $body);
        fseek($fp, 0);

        // Create signed request
        $request = new ServerRequest([], [], $uri, 'POST', $fp);
        $request = $request->withAttribute('vars', ['username' => $actor->username]);
        $signed = $signer->sign(
            $sk,
            $request,
            ['@method', '@path', 'host'],
            'https://minifedi.localhost/users/' . urlencode($actor->username) . '#main-key'
        );

        if (!($signed instanceof ServerRequestInterface)) {
            throw new TypeError('Unexpected return type');
        }
        try {
            $response = $handler->handle($signed);
            $this->assertSame(202, $response->getStatusCode(), 'Inbox post failed');
        } finally {
            $orchestration->flushAndUnstash();
        }
    }

    public function testRemoteActorInboxPost(): void
    {
        $orchestration = new Orchestration();
        $actorsTable = new Actors($orchestration->getDb());
        $handler = new Inbox();
        $signer = new HttpSignature();

        $orchestration->stash();
        $actor = $orchestration->createActor('phpunit-' . bin2hex(random_bytes(16)));
        $actor->summary = 'A dummy actor created for PHPUnit testing';
        $this->assertTrue($actorsTable->save($actor));
        $this->assertTrue($actor->hasPrimaryKey());

        $sk = SecretKey::generate();
        $pk = $sk->getPublicKey();

        $uri = 'https://minifedi.localhost/users/' . urlencode($actor->username) . '/inbox';
        $body = json_encode(['@context' => 'https://www.w3.org/ns/activitystreams', 'type' => 'Follow']);
        $fp = fopen('php://temp', 'w+');
        fwrite($fp, $body);
        fseek($fp, 0);

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/activity+json'], json_encode([
                'publicKey' => [
                    'publicKeyPem' => $pk->toString()
                ]
            ]))
        ]);
        $handlerStack = HandlerStack::create($mock);
        InboxTable::setMockClient(new Client(['handler' => $handlerStack]));

        // Create signed request
        $request = new ServerRequest([], [], $uri, 'POST', $fp);
        $request = $request->withAttribute('vars', ['username' => $actor->username]);
        $signed = $signer->sign(
            $sk,
            $request,
            ['@method', '@path', 'host'],
            'https://remote.example.com/actor#main-key'
        );

        if (!($signed instanceof ServerRequestInterface)) {
            throw new TypeError('Unexpected return type');
        }
        try {
            $response = $handler->handle($signed);
            $this->assertSame(202, $response->getStatusCode(), 'Inbox post failed');
        } finally {
            InboxTable::clearMockClient();
            $orchestration->flushAndUnstash();
        }
    }
}
