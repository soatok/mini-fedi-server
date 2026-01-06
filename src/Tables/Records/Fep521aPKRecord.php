<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Tables\Records;

use Soatok\MiniFedi\Exceptions\TableException;
use Soatok\MiniFedi\TableRecordInterface;
use Soatok\MiniFedi\Traits\TableRecordTrait;
use TypeError;

class Fep521aPKRecord implements TableRecordInterface
{
    use TableRecordTrait;

    public function __construct(
        public readonly ActorRecord $actor,
        public string $publicKey = '',
    ) {}

    public function fieldsToWrite(): array
    {
        return [
            'publickey' => $this->publicKey,
            'actor' => $this->actor->getPrimaryKey(),
        ];
    }
}
