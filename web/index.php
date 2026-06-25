<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/env.php';

if (!app_is_railway() && class_exists(\Dotenv\Dotenv::class) && file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__))->safeLoad();
}

$debug = app_env_bool('DEPLOY_DEBUG', app_env_bool('YII_DEBUG', app_env_bool('APP_DEBUG', false)));

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    ini_set('log_errors', '1');
}

defined('YII_DEBUG') or define('YII_DEBUG', $debug);
defined('YII_ENV') or define('YII_ENV', app_env('YII_ENV', app_env('APP_ENV', $debug ? 'dev' : 'prod')));

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
