<?php

return [
    'adminEmail' => 'support@aksespay.id',
    'senderEmail' => 'noreply@aksespay.id',
    'senderName' => 'AksesPay',
    'providers' => [
        'vip-reseller' => 'vipReseller',
        'vip-payment' => 'vipReseller',
    ],
    'paymentGateway' => 'ipaymu',
    'margins' => [
        'global' => ['user' => 2000, 'reseller' => 1000],
    ],
];
