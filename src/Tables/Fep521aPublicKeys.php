<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables;

use ReturnTypeWillChange;
use Soatok\MiniFedi\Exceptions\ConfigException;
use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\FediServerConfig;
use Soatok\MiniFedi\Table;
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Tables\Records\ActorRecord;
use Soatok\MiniFedi\Tables\Records\Fep521aPKRecord;
use TypeError;

class Fep521aPublicKeys extends Table
{

    public function tableName(): string
    {
        return 'minifedi_fep_521a_publickeys';
    }

    public function primaryKeyColumnName(): string
    {
        return 'publickeyid';
    }

    public function assertRecordType(TableRecordInterface $record): void
    {
        if (!($record instanceof Fep521aPKRecord)) {
            throw new TypeError('Incompatible object type: ' . get_class($record));
        }
    }

    #[ReturnTypeWillChange]
    public function newRecord(?TableRecordInterface $parent = null): Fep521aPKRecord
    {
        if (!($parent instanceof ActorRecord)) {
            throw new TypeError('Incompatible object type: ' . get_class($parent));
        }
        return new Fep521aPKRecord($parent);
    }

    /**
     * @return Fep521aPKRecord[]
     * @throws TableException
     */
    public function getPublicKeysFor(ActorRecord $actor): array
    {
        $rows = $this->db->run(
            "SELECT * FROM {$this->tableName()} WHERE actor = ?",
            $actor->getPrimaryKey()
        );
        $records = [];
        foreach ($rows as $row) {
            $records []= new Fep521aPKRecord(
                $actor,
                $row['publickey'],
                $row['keyid']
            );
        }
        return $records;
    }

    /**
     * @throws ConfigException
     * @throws TableException
     */
    public function getPublicKey(ActorRecord $actor, string $keyId): Fep521aPKRecord
    {
        // This is grossly inefficient, but it's only for testing.
        $keys = $this->getPublicKeysFor($actor);
        if (empty($keys)) {
            throw new TableException('No public keys found for this actor');
        }
        foreach ($keys as $key) {
            if ($key->keyId === $keyId) {
                return $key;
            }
        }
        throw new TableException('No public key found for this Key ID');
    }
}
