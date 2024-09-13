<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\Contracts\SmsServiceInterface;

class SmsChannel
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toSms')) {
            $message = $notification->toSms($notifiable);

            // Envoyer le SMS via le service configuré
            $this->smsService->sendSmsToClient(
                $message['to'],
                $message['amount'],
                $message['client_name']
            );
        }
    }
}
