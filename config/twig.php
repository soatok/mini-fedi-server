<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Config;

use Twig\{
    Environment,
    Loader\FilesystemLoader
};

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/twig.php')) {
    return require_once __DIR__ . '/local/twig.php';
}

return new Environment(
    new FilesystemLoader(dirname(__DIR__) . '/templates')
);
