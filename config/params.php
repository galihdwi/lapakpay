<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'providers' => [
        'vip-reseller' => 'vipReseller',
        'vip-payment' => 'vipReseller',
    ],
    'paymentGateway' => 'ipaymu',
    'margins' => [
        'global' => ['user' => 2000, 'reseller' => 1000],
    ],
];
