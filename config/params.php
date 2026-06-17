<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'providers' => [
        'vip-reseller' => 'vipReseller',
    ],
    'paymentGateway' => 'mayar',
    'margins' => [
        'global' => ['user' => 2000, 'reseller' => 1000],
    ],
];
