<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Config;

use Soatok\MiniFedi\FediServerConfig;

require_once __DIR__ . '/vendor/autoload.php';

define('MINIFEDI_BASE_DIR', __DIR__);

// Store in singleton
FediServerConfig::instance()
    ->withDatabase(require_once __DIR__ . '/config/database.php')
    ->withRouter(require_once __DIR__ . '/config/routes.php')
    ->withServerSecretKey(require_once __DIR__ . '/config/secret_key.php')
    ->withRuntimeVars(require_once __DIR__ . '/config/vars.php')
    ->withTwig(require_once __DIR__ . '/config/twig.php')
;
