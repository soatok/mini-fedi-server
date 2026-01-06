<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables;

use FediE2EE\PKD\Crypto\Exceptions\{
    HttpSignatureException,
    NotImplementedException
};
use FediE2EE\PKD\Crypto\HttpSignature;
use Psr\Http\Message\ServerRequestInterface;
use ReturnTypeWillChange;
use Soatok\MiniFedi\Exceptions\{
    ConfigException,
    InvalidRequestException,
    TableException
};
use Soatok\MiniFedi\Table;
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Tables\Records\{
    ActorRecord,
    InboxRecord
};
use SodiumException;
use TypeError;

class InboxTable extends Table
{
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
        // 1. Get the public key for the signature.
        $pkTable = new Fep521aPublicKeys($this->db);
        $signatureInput = $request->getHeaderLine('Signature-Input');
        if (!preg_match('/keyid="([^"]+)"/', $signatureInput, $matches)) {
            throw new InvalidRequestException('No keyId found in Signature-Input header');
        }
        $keyId = $matches[1];
        $publicKey = $pkTable->getPublicKey($actor, $keyId);

        // 2. Verify the signature.
        $httpSig = new HttpSignature();

        // 2. Verify the signature.
        if (!$httpSig->verify($publicKey->toCryptoKey(), $request)) {
            throw new InvalidRequestException('Invalid HTTP signature');
        }

        // 3. Save to inbox.
        $record = $this->newRecord($actor);
        $record->message = (string) $request->getBody();
        return $this->save($record);
    }

    public function tableName(): string
    {
        return 'minifedi_inbox';
    }

    public function primaryKeyColumnName(): string
    {
        return 'inboxid';
    }

    public function assertRecordType(TableRecordInterface $record): void
    {
        if (!($record instanceof InboxRecord)) {
            throw new TypeError('Incompatible object type: ' . get_class($record));
        }
    }

    #[ReturnTypeWillChange]
    public function newRecord(?TableRecordInterface $parent = null): InboxRecord
    {
        if (!($parent instanceof ActorRecord)) {
            throw new TypeError('Incompatible object type: ' . get_class($parent));
        }
        return new InboxRecord($parent);
    }
}
