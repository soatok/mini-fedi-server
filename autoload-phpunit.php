<?php
declare(strict_types=1);
namespace Soatok\MiniFedi;

use ParagonIE\EasyDB\EasyDBCache;
use PDO;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/autoload.php';

(function () {
    $conf = FediServerConfig::instance();
    $driver = $conf->database()->getDriver();
    if ($driver === 'sqlite') {
        if (!is_dir(__DIR__ . '/tmp/db/')) {
            mkdir(__DIR__ . '/tmp/db/');
        }
        $temp = __DIR__ . '/tmp/db/' . sodium_bin2hex(random_bytes(16)) . '-test.db';
        $conf->withDatabase(new EasyDBCache(new PDO('sqlite:' . $temp)));
    }
    $db = $conf->database();
    $sql = file_get_contents(__DIR__ . '/sql/' . $driver . '/mini-fedi.sql');
    $db->beginTransaction();
    $db->exec($sql);
    if (!$db->commit()) {
        die($db->errorInfo()[2]);
    }
})();
