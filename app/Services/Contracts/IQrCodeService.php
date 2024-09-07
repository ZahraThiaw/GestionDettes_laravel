<?php

namespace App\Services\Contracts;

interface IQrCodeService
{
    public function generateQrCode($phoneNumber);
}
