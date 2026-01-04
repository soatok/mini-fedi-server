<?php
declare(strict_types=1);
namespace Soatok\MiniFedi;

use ParagonIE\EasyDB\EasyDB;
use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\Tables\Records\ActorRecord;

abstract class Table
{
    abstract public function tableName(): string;

    abstract public function primaryKeyColumnName(): string;

    /**
     * Throws if record is not the correct type for this table.
     */
    abstract public function assertRecordType(TableRecordInterface $record): void;

    /**
     * Override me.
     *
     * @return string
     */
    public function primaryKeySequenceId(): string
    {
        $db = $this->db();
        if ($db->getDriver() === 'pgsql') {
            return $this->tableName() . '_' . $this->primaryKeyColumnName() . '_seq';
        }
        return '';
    }

    public function db(): EasyDB
    {
        $config = FediServerConfig::instance();
        return $config->database();
    }

    public function save(TableRecordInterface $record): bool
    {
        $this->assertRecordType($record);
        $db = $this->db();
        $db->beginTransaction();
        if ($record->hasPrimaryKey()) {
            $primaryKey = $record->getPrimaryKey();
            $db->update(
                $this->tableName(),
                $record->fieldsToWrite(),
                [$this->primaryKeyColumnName() => $primaryKey],
            );
        } else {
            $primaryKey = $db->insertReturnId(
                $this->tableName(),
                $record->fieldsToWrite(),
                $this->primaryKeySequenceId(),
            );
            if (is_numeric($primaryKey)) {
                $primaryKey = (int) $primaryKey;
            }
            $record->setPrimaryKey($primaryKey);
        }
        return $db->commit();
    }

    abstract public function newRecord(): TableRecordInterface;
}
