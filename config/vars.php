<?php

declare(strict_types=1);
namespace Soatok\MiniFedi\Config;

use FediE2EE\PKD\Crypto\SecretKey;
use ParagonIE\ConstantTime\Base64UrlSafe;
use ParagonIE\EasyDB\EasyDBCache;
use Soatok\MiniFedi\RuntimeVars;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/vars.php')) {
    return require_once __DIR__ . '/local/vars.php';
}

if (file_exists(__DIR__ . '/vars.json')) {
    $file = file_get_contents(__DIR__ . '/vars.json');
    if (!is_string($file)) {
        throw new \Exception('Cannot read variables');
    }
    return RuntimeVars::fromJson($file);
}
$vars = new RuntimeVars();
file_put_contents(__DIR__ . '/vars.json', $vars->toJson());
return $vars;
