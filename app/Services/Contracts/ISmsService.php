<?php

namespace App\Services\Contracts;

interface ISmsService
{
    public function sendSms($phoneNumber, $message);
}
