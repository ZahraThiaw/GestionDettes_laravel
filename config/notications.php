<?php
return [
    
    'channels' => [
        'database' => \Illuminate\Notifications\Channels\DatabaseChannel::class,
        'sms' => \App\Notifications\Channels\SmsChannel::class,
    ],
];
