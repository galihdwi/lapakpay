<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

if (class_exists(\Dotenv\Dotenv::class) && file_exists(__DIR__ . '/../.env')) {
    \Dotenv\Dotenv::createUnsafeImmutable(dirname(__DIR__))->safeLoad();
}

require __DIR__ . '/../config/env.php';

defined('YII_DEBUG') or define('YII_DEBUG', app_env_bool('YII_DEBUG', false));
defined('YII_ENV') or define('YII_ENV', app_env('YII_ENV', 'prod'));

require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
