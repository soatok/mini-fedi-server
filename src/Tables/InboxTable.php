<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables;

use ReturnTypeWillChange;
use Soatok\MiniFedi\Table;
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Tables\Records\ActorRecord;
use Soatok\MiniFedi\Tables\Records\InboxRecord;
use TypeError;

class InboxTable extends Table
{

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
