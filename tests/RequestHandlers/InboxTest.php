<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tests\RequestHandlers;

use FediE2EE\PKD\Crypto\HttpSignature;
use FediE2EE\PKD\Crypto\SecretKey;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Soatok\MiniFedi\Orchestration;
use Soatok\MiniFedi\RequestHandlers\Inbox;
use Soatok\MiniFedi\Tables\Actors;
use Soatok\MiniFedi\Tables\Fep521aPublicKeys;
use TypeError;

#[CoversClass(Inbox::class)]
class InboxTest extends TestCase
{
    public function testInboxPost(): void
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
        $pkRecord = $orchestration->createPublicKeyForActor($actor, $pk->toString());

        $uri = '/users/' . urlencode($actor->username). '/inbox';
        $body = json_encode(['@context' => 'https://www.w3.org/ns/activitystreams', 'type' => 'Follow']);
        $fp = fopen('php://temp', 'w+');
        fwrite($fp, $body);
        fseek($fp, 0);

        // Create signed request
        $request = new ServerRequest([], [], $uri, 'POST', $fp);
        $request = $request->withAttribute('username', $actor->username);
        $signed = $signer->sign(
            $sk,
            $request,
            ['@method', '@path', 'host'],
            $pkRecord->keyId
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
}
