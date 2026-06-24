<?php

require_once __DIR__ . '/env.php';

return [
    'class' => \yii\mongodb\Connection::class,
    'dsn' => app_env('MONGODB_DSN', 'mongodb://localhost:27017/lapakpay'),
    'defaultDatabaseName' => app_env('MONGODB_DATABASE', 'lapakpay'),
];
