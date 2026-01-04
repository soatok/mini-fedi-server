<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tests\RequestHandlers;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Soatok\MiniFedi\RequestHandlers\Users;
use Soatok\MiniFedi\Tables\Actors;
use Soatok\MiniFedi\Tables\Records\ActorRecord;

#[CoversClass(Users::class)]
class UsersTest extends TestCase
{
    public function testProfileInfo(): void
    {
        $actorsTable = new Actors();
        $newRecord = $actorsTable->newRecord();
        $this->assertInstanceOf(ActorRecord::class, $newRecord);
        $test = bin2hex(random_bytes(16));
        $newRecord->username = 'phpunit-' . $test;
        $newRecord->displayName = 'PHPUnit Test Actor ' . $test;
        $newRecord->summary = 'A dummy actor created for PHPUnit testing';
        $this->assertTrue($actorsTable->save($newRecord));

        // If you don't set the header, you get HTML
        $request = (new ServerRequest([], [], '/users/' . $newRecord->username, 'GET'))
            ->withAttribute('username', $newRecord->username)
            ->withHeader('Accept', 'text/html');
        $handler = new Users();
        $response = $handler->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('text/html; charset=utf-8', $response->getHeader('Content-Type')[0]);

        // If you do, you get JSON

        $request2 = (new ServerRequest([], [], '/users/' . $newRecord->username, 'GET'))
            ->withAttribute('username', $newRecord->username)
            ->withHeader('Accept', 'application/activity+json');

        $response2 = $handler->handle($request2);
        $this->assertSame(200, $response2->getStatusCode());
        $this->assertSame('application/activity+json', $response2->getHeader('Content-Type')[0]);
    }
}
