<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Config;

use Soatok\MiniFedi\RequestHandlers\{
    WebFinger
};
use League\Route\Router;
use League\Route\RouteGroup;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/routes.php')) {
    return require_once __DIR__ . '/local/routes.php';
}
$router = new Router();

/*
$router->group('/api', function(RouteGroup $r) use ($router) {

});
*/
$router->map('GET', '/.well-known/webfinger', WebFinger::class);

return $router;
