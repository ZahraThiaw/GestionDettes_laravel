<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\Contracts\SmsServiceInterface;

class SmsDemandeChannel
{
    protected $smsService;

    public function __construct(SmsServiceInterface $smsService)
    {
        $this->smsService = $smsService;
    }

    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toSms')) {
            $messagesms = $notification->toSms($notifiable);

            // Envoyer le SMS via le service configuré
            $this->smsService->sendSmsToClient(
                $messagesms['to'],
                $messagesms['message'],
                $messagesms['client'],
                $messagesms['articles']
            );
        }
    }
}
