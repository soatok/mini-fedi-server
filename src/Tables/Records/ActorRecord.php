<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables\Records;

use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\TableRecordInterface;
use TypeError;

class ActorRecord implements TableRecordInterface
{
    private ?int $primaryKey = null;

    public function __construct(
        public string $username = '',
        public string $displayName = '',
        public string $type = 'Person',
        public string $summary = '',
        public string $preferredUsername = '',
    ) {}

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

    public function fieldsToWrite(): array
    {
        return [
            'username' => $this->username,
            'preferredUsername' => $this->preferredUsername,
            'name' => $this->displayName,
            'summary' => $this->summary,
            'actortype' => $this->type,
        ];
    }
}
