<?php

require_once __DIR__ . '/env.php';

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => app_env('COOKIE_VALIDATION_KEY', 'Yh192imvkahVRfiZa1egUZVJAKhLZvfK'),
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'i18n' => [
            'translations' => [
                'yii/bootstrap5' => [
                    'class' => \yii\i18n\GettextMessageSource::class,
                    'sourceLanguage' => 'en-US',
                    'basePath' => '@yii/bootstrap5/messages',
                ],
            ],
        ],
        'queue' => [
            'class' => \yii\queue\file\Queue::class,
            'path' => '@runtime/queue',
        ],
        'providerRegistry' => [
            'class' => \app\services\ProviderRegistry::class,
        ],
        'vipReseller' => [
            'class' => \app\providers\VipResellerProvider::class,
            'apiUrl' => app_env('VIP_RESELLER_API_URL', 'https://vip-reseller.co.id/api/'),
            'apiId' => app_env('VIP_RESELLER_API_ID'),
            'apiKey' => app_env('VIP_RESELLER_API_KEY'),
        ],
        'mayarGateway' => [
            'class' => \app\gateways\MayarGateway::class,
            'apiUrl' => app_env('MAYAR_API_URL', 'https://api.mayar.id/'),
            'apiKey' => app_env('MAYAR_API_KEY'),
        ],
        'flipGateway' => [
            'class' => \app\gateways\FlipGateway::class,
            'apiUrl' => app_env('FLIP_API_URL', 'https://bigflip.id/big_sandbox_api/v2/'),
            'apiKey' => app_env('FLIP_API_KEY'),
            'validationToken' => app_env('FLIP_VALIDATION_TOKEN'),
            'publicBaseUrl' => app_env('APP_BASE_URL'),
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                    'logFile' => 'php://stderr',
                    'exportInterval' => 1,
                ],
            ],
        ],
        'mongodb' => $db,
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                'games/<slug:[\w-]+>' => 'site/products',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV && class_exists(\yii\debug\Module::class)) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

if (YII_ENV_DEV && class_exists(\yii\gii\Module::class)) {
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
