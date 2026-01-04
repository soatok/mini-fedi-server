<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Config;

use FediE2EE\PKD\Crypto\SecretKey;
use ParagonIE\ConstantTime\Base64UrlSafe;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/secret_key.php')) {
    return require_once __DIR__ . '/local/secret_key.php';
}

if (file_exists(__DIR__ . '/secret.key')) {
    $file = file_get_contents(__DIR__ . '/secret.key');
    if (!is_string($file)) {
        throw new \Exception('Cannot read signing keys');
    }
    return new SecretKey(Base64UrlSafe::decodeNoPadding($file));
} else {
    $sk = SecretKey::generate();
    file_put_contents(
        __DIR__ . '/secret.key',
        Base64UrlSafe::encodeUnpadded($sk->getBytes())
    );
    return $sk;
}
