<?php

require_once __DIR__ . '/env.php';

if (!function_exists('app_mongodb_dsn')) {
    function app_mongodb_dsn(): string
    {
        return app_env(
            'MONGODB_DSN',
            app_env('MONGO_URL', app_env('DATABASE_URL', 'mongodb://localhost:27017/lapakpay')),
        );
    }
}

if (!function_exists('app_mongodb_database')) {
    function app_mongodb_database(string $dsn): string
    {
        $configuredDatabase = app_env('MONGODB_DATABASE', app_env('MONGO_DATABASE', ''));
        if ($configuredDatabase !== '') {
            return $configuredDatabase;
        }

        $path = parse_url($dsn, PHP_URL_PATH);
        if (is_string($path) && trim($path, '/') !== '') {
            return trim($path, '/');
        }

        return 'lapakpay';
    }
}

$dsn = app_mongodb_dsn();

return [
    'class' => \yii\mongodb\Connection::class,
    'dsn' => $dsn,
    'defaultDatabaseName' => app_mongodb_database($dsn),
];
