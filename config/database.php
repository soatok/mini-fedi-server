<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Config;

use ParagonIE\EasyDB\EasyDBCache;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/database.php')) {
    return require_once __DIR__ . '/local/database.php';
}

return new EasyDBCache('sqlite:', __DIR__ . '/database.sqlite');
