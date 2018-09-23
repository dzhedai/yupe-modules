<?php

return [
    'module' => [
        'class' => 'application.modules.smsru.SmsruModule',
    ],
    'import' => [
        'application.modules.smsru.listeners.*',
    ],
    'component' => [
        'eventManager' => [
            'class' => 'yupe\components\EventManager',
            'events' => [
                'callback.add' => [
                    ['SmsruCallbackListener', 'onCallbackAdd']
                ],
                'order.created' => [
                    ['SmsruOrderListener', 'onOrderAdd']
                ]
            ],
        ],
    ],
    'rules'     => [
        '/smsru/send' => '/smsru/smsru/send',
        '/smsru/verify' => '/smsru/smsru/verify'
    ],
];
