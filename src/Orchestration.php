<?php
declare(strict_types=1);
namespace Soatok\MiniFedi;

use GuzzleHttp\Client;
use ParagonIE\EasyDB\EasyDB;
use Soatok\MiniFedi\Exceptions\BaseException;
use Soatok\MiniFedi\Tables\{
    Actors,
    Fep521aPublicKeys,
    InboxTable,
    OutboxTable
};
use Soatok\MiniFedi\Tables\Records\{
    ActorRecord,
    Fep521aPKRecord,
    InboxRecord,
    OutboxRecord
};
use Soatok\MiniFedi\Exceptions\ConfigException;
use Soatok\MiniFedi\Exceptions\TableException;

class Orchestration
{
    protected EasyDB $db;
    protected Actors $actors;
    protected InboxTable $inbox;
    protected OutboxTable $outbox;
    protected Fep521aPublicKeys $fep521aPublicKeys;
    protected Client $guzzle;
    protected array $stash = [];

    /**
     * @throws ConfigException
     */
    public function __construct(?EasyDB $db = null, ?Client $guzzle = null)
    {
        $this->db = $db ?? FediServerConfig::instance()->database();
        $this->guzzle = $guzzle ?? new Client();

        $this->actors = new Actors($this->db);
        $this->fep521aPublicKeys = new Fep521aPublicKeys($this->db);
        $this->inbox = new InboxTable($this->db);
        $this->outbox = new OutboxTable($this->db);
    }

    /**
     * Dump the current tables to a stash
     *
     * @api
     */
    public function stash(): bool
    {
        $filename = tempnam(
            MINIFEDI_BASE_DIR . '/tmp/',
            'dump_' . bin2hex(random_bytes(16))
        );
        if (!$this->dumpToFile($filename)) {
            return false;
        }
        array_push($this->stash, $filename);
        return $this->truncateAllTables();
    }

    /**
     * Restore from the most recent stash
     *
     * @api
     * @throws BaseException
     */
    public function unstash(): bool
    {
        if (empty($this->stash)) {
            throw new BaseException('No stashes available to restore');
        }
        $filename = array_pop($this->stash);
        $this->truncateAllTables();
        if (!$this->loadFromFile($filename)) {
            throw new BaseException('Could not unstash');
        }
        unlink($filename);
        return true;
    }

    /**
     * @throws BaseException
     */
    public function createActor(
        string $username,
        string $displayName = '',
        string $preferredUsername = '',
        string $summary = ''
    ): ActorRecord {
        $record = $this->actors->newRecord();
        $record->username = $username;
        if (empty($displayName)) {
            $displayName = $username;
        }
        if (empty($preferredUsername)) {
            $preferredUsername = $username;
        }
        $record->preferredUsername = $preferredUsername;
        $record->displayName = $displayName;
        $record->summary = $summary;
        if (!$this->actors->save($record)) {
            throw new BaseException('Could not save actor');
        }
        return $record;
    }

    /**
     * @throws BaseException
     *
     * @api
     */
    public function createPublicKeyForActor(ActorRecord $parent, string $publicKey): Fep521aPKRecord
    {
        $record = $this->fep521aPublicKeys->newRecord($parent);
        $record->publicKey = $publicKey;
        if (!$this->fep521aPublicKeys->save($record)) {
            throw new BaseException('Could not save public key');
        }
        return $record;
    }

    /**
     * @api
     * @throws BaseException
     */
    public function createInboxMessage(ActorRecord $parent, string $message): InboxRecord
    {
        $record = $this->inbox->newRecord($parent);
        $record->message = $message;
        if (!($this->inbox->save($record))) {
            throw new BaseException('Could not save inbox message');
        }
        return $record;
    }


    /**
     * @api
     * @throws BaseException
     */
    public function createOutboxMessage(ActorRecord $parent, string $message): OutboxRecord
    {
        $record = $this->outbox->newRecord($parent);
        $record->message = $message;
        if (!($this->outbox->save($record))) {
            throw new BaseException('Could not save inbox message');
        }
        return $record;
    }

    /**
     * @api
     * @throws TableException
     */
    public function getActor(string $username): ActorRecord
    {
        return $this->actors->getActorInfo($username);
    }

    /**
     * @api
     * @throws TableException
     */
    public function getPublicKeyForActor(ActorRecord $actor): array
    {
        return $this->fep521aPublicKeys->getPublicKeysFor($actor);
    }

    public function dumpToFile(string $filename): bool
    {
        $written = file_put_contents($filename, $this->exportJson());
        return is_int($written);
    }

    public function exportArray(): array
    {
        return [
            'minifedi_actors' =>
                $this->db->run("SELECT * FROM minifedi_actors"),
            'minifedi_fep_521a_publickeys' =>
                $this->db->run("SELECT * FROM minifedi_fep_521a_publickeys"),
            'minifedi_inbox' =>
                $this->db->run("SELECT * FROM minifedi_inbox"),
            'minifedi_outbox' =>
                $this->db->run("SELECT * FROM minifedi_outbox"),
        ];
    }

    public function exportJson(): string
    {
        return json_encode(
            $this->exportArray(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );
    }

    public function loadFromFile(string $filename): bool
    {
        $file = file_get_contents($filename);
        if (!is_string($file)) {
            return false;
        }
        return $this->importJson($file);
    }

    public function importJson(string $contents): bool
    {
        $data = json_decode($contents, true);
        return $this->importArray($data);
    }

    public function importArray(array $data): bool
    {
        $this->db->beginTransaction();
        foreach (array_keys($data) as $table) {
            $rows = $data[$table];
            foreach ($rows as $row) {
                $fields = [];
                foreach ($row as $column => $value) {
                    $fields[$column] = $value;
                }
                $this->db->insert($table, $fields);
            }
        }
        return $this->db->commit();
    }

    /**
     * @throws BaseException
     */
    protected function truncateAllTables(): bool
    {
        $driver = $this->db->getDriver();
        $tables = [
            'minifedi_inbox',
            'minifedi_outbox',
            'minifedi_fep_521a_publickeys',
            'minifedi_actors',
        ];

        $this->db->beginTransaction();
        try {
            if ($driver === 'mysql') {
                $this->db->exec('SET FOREIGN_KEY_CHECKS=0');
                foreach ($tables as $table) {
                    $this->db->exec("TRUNCATE TABLE {$table}");
                }
                $this->db->exec('SET FOREIGN_KEY_CHECKS=1');
            } elseif ($driver === 'pgsql') {
                $tableList = implode(', ', $tables);
                $this->db->exec("TRUNCATE TABLE {$tableList} CASCADE");
            } elseif ($driver === 'sqlite') {
                foreach ($tables as $table) {
                    $this->db->exec("DELETE FROM {$table}");
                }
            } else {
                throw new BaseException("Unsupported database driver: {$driver}");
            }
            return $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw new BaseException("Failed to truncate tables: " . $e->getMessage());
        }
    }
}
