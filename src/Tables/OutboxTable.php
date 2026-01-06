<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables;

use ReturnTypeWillChange;
use Soatok\MiniFedi\Table;
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Tables\Records\ActorRecord;
use Soatok\MiniFedi\Tables\Records\OutboxRecord;
use TypeError;

class OutboxTable extends Table
{
    public function tableName(): string
    {
        return 'minifedi_outbox';
    }

    public function primaryKeyColumnName(): string
    {
        return 'outboxid';
    }

    public function assertRecordType(TableRecordInterface $record): void
    {
        if (!($record instanceof OutboxRecord)) {
            throw new TypeError('Incompatible object type: ' . get_class($record));
        }
    }

    #[ReturnTypeWillChange]
    public function newRecord(?TableRecordInterface $parent = null): OutboxRecord
    {
        if (!($parent instanceof ActorRecord)) {
            throw new TypeError('Incompatible object type: ' . get_class($parent));
        }
        return new OutboxRecord($parent);
    }
}
