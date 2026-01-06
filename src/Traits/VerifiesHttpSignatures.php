<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Traits;

use FediE2EE\PKD\Crypto\Exceptions\{
    HttpSignatureException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\{
    HttpSignature,
    PublicKey
};
use Psr\Http\Message\ServerRequestInterface;
use Soatok\MiniFedi\Exceptions\{
    InvalidRequestException,
    ConfigException,
    TableException
};
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Tables\Fep521aPublicKeys;
use Soatok\MiniFedi\Tables\Records\ActorRecord;
use GuzzleHttp\Client;
use SodiumException;
use Soatok\MiniFedi\Table;

/**
 * @property Client $http
 * @method self newRecord(?TableRecordInterface $parent = null)
 * @method bool save(TableRecordInterface $record)
 */
trait VerifiesHttpSignatures
{
    protected static ?Client $mockClient = null;

    public static function setMockClient(Client $client): void
    {
        self::$mockClient = $client;
    }

    public static function clearMockClient(): void
    {
        self::$mockClient = null;
    }

    /**
     * @throws ConfigException
     * @throws HttpSignatureException
     * @throws InvalidRequestException
     * @throws NotImplementedException
     * @throws SodiumException
     * @throws TableException
     */
    public function accept(ServerRequestInterface $request, ActorRecord $actor): bool
    {
        if (!$actor->hasPrimaryKey()) {
            throw new InvalidRequestException('Actor has not been saved yet');
        }

        // 1. Get the keyId from the signature header.
        $signatureInput = $request->getHeaderLine('Signature-Input');
        if (!preg_match('/keyid="([^"]+)"/', $signatureInput, $matches)) {
            throw new InvalidRequestException('No keyId found in Signature-Input header');
        }
        $keyId = $matches[1];

        // 2. Fetch the public key.
        $keyIdUrl = parse_url($keyId);
        if (is_array($keyIdUrl) && !empty($keyIdUrl['host'])) {
            if (hash_equals($keyIdUrl['host'], $request->getUri()->getHost())) {
                // Local actor
                $pkTable = new Fep521aPublicKeys($this->db);
                $publicKey = $pkTable->getPublicKey($actor, $keyId)->toCryptoKey();
            } else {
                // Remote actor
                $client = self::$mockClient ?? new Client();
                $response = $client->get($keyId, [
                    'headers' => [
                        'Accept' =>
                            'application/ld+json; profile="https://www.w3.org/ns/activitystreams", application/activity+json'
                    ]
                ]);
                $body = (string) $response->getBody();
                $data = json_decode($body, true);
                if (!is_array($data)) {
                    throw new InvalidRequestException('Invalid JSON in actor profile');
                }
                if (!isset($data['publicKey']['publicKeyPem'])) {
                    throw new InvalidRequestException('Public key not found in actor profile');
                }
                $publicKeyPem = $data['publicKey']['publicKeyPem'];
                $publicKey = PublicKey::fromString($publicKeyPem);
            }
        } else {
            throw new InvalidRequestException('Invalid keyId format');
        }

        // 3. Verify the signature.
        $httpSig = new HttpSignature();
        if (!$httpSig->verify($publicKey, $request)) {
            throw new InvalidRequestException('Invalid HTTP signature');
        }

        // 4. Save to inbox/outbox.
        /** @var Table $this */
        $record = $this->newRecord($actor);
        $record->message = (string) $request->getBody();
        return $this->save($record);
    }
}
