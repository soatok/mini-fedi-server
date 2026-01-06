<?php
declare(strict_types=1);
namespace Soatok\MiniFedi\Config;

use Soatok\MiniFedi\RequestHandlers\{
    Inbox,
    Outbox,
    Users,
    WebFinger
};
use League\Route\Router;

/* Defer to local config (if applicable) */
if (file_exists(__DIR__ . '/local/routes.php')) {
    return require_once __DIR__ . '/local/routes.php';
}
$router = new Router();
$router->map('GET', '/.well-known/webfinger', WebFinger::class);
$router->map('GET', '/users/{username}', Users::class);
$router->map(['GET', 'POST'], '/users/{username}/inbox', Inbox::class);
$router->map(['GET', 'POST'], '/users/{username}/outbox', Outbox::class);
return $router;
