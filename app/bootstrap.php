<?php

function app_base_path(): string
{
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $directory = dirname($scriptName);
    return $directory === '/' || $directory === '.' ? '' : rtrim($directory, '/');
}

function route_url(string $route = ''): string
{
    return app_base_path() . '/' . ltrim($route, '/');
}

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $path = __DIR__ . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)))
        . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/PayMongo.php';
require_once __DIR__ . '/../includes/product_notifications.php';
