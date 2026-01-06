<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables\Records;

use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Traits\TableRecordTrait;

class OutboxRecord implements TableRecordInterface
{
    use TableRecordTrait;

    public function __construct(
        public readonly ActorRecord $actor,
        public string $message = '',
        public bool $processed = false,
        public bool $read = false,
    ) {}

    public function fieldsToWrite(): array
    {
        return [
            'message' => $this->message,
            'processed' => $this->processed,
            'read' => $this->read,
            'actor' => $this->actor->getPrimaryKey(),
        ];
    }
}
