<?php
declare(strict_types=1);
namespace Soatok\MiniFedi;

interface TableRecordInterface
{
    public function hasPrimaryKey(): bool;
    public function getPrimaryKey(): int|string;
    public function setPrimaryKey(int|string $primaryKey): self;
    public function fieldsToWrite(): array;
}
