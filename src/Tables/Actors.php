<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables;

use Override;
use ReturnTypeWillChange;
use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\Table;
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Tables\Records\ActorRecord;
use TypeError;

class Actors extends Table
{
    public function tableName(): string
    {
        return 'minifedi_actors';
    }

    public function primaryKeyColumnName(): string
    {
        return 'actorid';
    }

    public function assertRecordType(TableRecordInterface $record): void
    {
        if (!($record instanceof ActorRecord)) {
            throw new TypeError('Incompatible object type: ' . get_class($record));
        }
    }

    public function getActorInfo(string $username): ActorRecord
    {
        $row = $this->db->row(
            "SELECT * FROM {$this->tableName()} WHERE username = ?",
            $username
        );
        if (empty($row)) {
            throw new TableException('Actor not found:' .  $username);
        }
        $actor = new ActorRecord(
            $row['username'],
            $row['name'],
            $row['actortype'],
            $row['summary'],
            $row['preferredUsername'],
        );
        $actor->setPrimaryKey((int) $row['actorid']);
        return $actor;
    }

    /**
     * @throws TableException
     */
    public function getActorById(int $actorId): ActorRecord
    {
        $row = $this->db->row(
            "SELECT * FROM {$this->tableName()} WHERE actorid = ?",
            $actorId
        );
        if (empty($row)) {
            throw new TableException('Actor not found: ' .  $actorId);
        }
        $record = new ActorRecord(
            $row['username'],
            $row['name'],
            $row['actortype'],
            $row['summary'],
            $row['preferredUsername'],
        );
        $record->setPrimaryKey($row['actorid']);
        return $record;
    }

    #[Override]
    #[ReturnTypeWillChange]
    public function newRecord(?TableRecordInterface $parent = null): ActorRecord
    {
        if (!is_null($parent)) {
            throw new TableException('Actor Has no parent');
        }
        return new ActorRecord();
    }
}
