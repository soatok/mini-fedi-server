<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Traits;

use Soatok\MiniFedi\Exceptions\TableException;
use TypeError;

trait TableRecordTrait
{
    private ?int $primaryKey = null;
    public function hasPrimaryKey(): bool
    {
        return !is_null($this->primaryKey);
    }

    public function getPrimaryKey(): int|string
    {
        if (is_null($this->primaryKey)) {
            throw new TableException('Primary Key has not been set');
        }
        return $this->primaryKey;
    }

    public function setPrimaryKey(int|string $primaryKey): self
    {
        if (is_string($primaryKey)) {
            throw new TypeError('Primary Key must be integer');
        }
        $this->primaryKey = $primaryKey;
        return $this;
    }
}
