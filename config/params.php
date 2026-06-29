<?php

require_once __DIR__ . '/env.php';

return [
    'adminEmail' => 'support@aksespay.com',
    'senderEmail' => 'noreply@aksespay.com',
    'senderName' => 'AksesPay',
    'resend' => [
        'apiKey' => app_env('RESEND_API_KEY'),
        'fromEmail' => app_env('RESEND_FROM_EMAIL', app_env('MAIL_FROM_EMAIL', 'noreply@aksespay.com')),
        'fromName' => app_env('RESEND_FROM_NAME', app_env('MAIL_FROM_NAME', 'AksesPay')),
    ],
    'providers' => [
        'vip-reseller' => 'vipReseller',
        'vip-payment' => 'vipReseller',
    ],
    'paymentGateway' => 'ipaymu',
    'margins' => [
        'global' => ['user' => 2000, 'reseller' => 1000],
    ],
];
