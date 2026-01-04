<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\PublicWebRoot;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Soatok\MiniFedi\FediServerConfig;
use Throwable;

require_once dirname(__DIR__) . '/autoload.php';

$miniFediConfig = FediServerConfig::instance();
try {
    $router = $miniFediConfig->router();
    $request = ServerRequestFactory::fromGlobals();
    (new SapiEmitter)->emit(
        $router->dispatch($request)
    );
} catch (Throwable $ex) {
    http_response_code(500);
    if ($miniFediConfig->vars()->debug) {
        header('Content-Type: text/plain');
        echo $ex->getMessage(), PHP_EOL;
        echo 'Code: ', $ex->getCode(), PHP_EOL;
        echo str_repeat('-', 76), PHP_EOL;
        echo $ex->getTraceAsString(), PHP_EOL;
    }
    exit(1);
}
