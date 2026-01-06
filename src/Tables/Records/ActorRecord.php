<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables\Records;

use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Traits\TableRecordTrait;
use TypeError;

class ActorRecord implements TableRecordInterface
{
    use TableRecordTrait;

    public function __construct(
        public string $username = '',
        public string $displayName = '',
        public string $type = 'Person',
        public string $summary = '',
        public string $preferredUsername = '',
    ) {}

    public function toArray(): array
    {
        return [
            'actorid' => $this->primaryKey,
            'username' => $this->username,
            'preferredUsername' => $this->preferredUsername,
            'name' => $this->displayName,
            'summary' => $this->summary,
            'actortype' => $this->type,
        ];
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
