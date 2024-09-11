<?php

namespace App\Services;

use App\Services\Contracts\IQrCodeService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService implements IQrCodeService
{
    public function generateQrCode($phoneNumber)
    {
        $qrCode = QrCode::create($phoneNumber)->setSize(300)->setMargin(10);
        $writer = new PngWriter();

        // Générer le QR code en tant que chaîne PNG
        $result = $writer->write($qrCode);

        // Convertir le QR code en base64
        $base64QrCode = base64_encode($result->getString());

        // Retourner la chaîne base64
        return 'data:image/png;base64,' . $base64QrCode;
    }
}


// namespace App\Services;

// use App\Services\Contracts\IQrCodeService;
// use Endroid\QrCode\QrCode;
// use Endroid\QrCode\Writer\PngWriter;
// use Illuminate\Support\Facades\Storage;

// class QrCodeService implements IQrCodeService
// {
//     public function generateQrCode($phoneNumber)
//     {
//         $qrCode = QrCode::create($phoneNumber)->setSize(300)->setMargin(10);
//         $writer = new PngWriter();
//         $qrCodeFileName = 'qrcodes/' . $phoneNumber . '.png';
//         $filePath = public_path('storage/' . $qrCodeFileName);

//         $result = $writer->write($qrCode);
//         Storage::disk('public')->put($qrCodeFileName, $result->getString());

//         return $qrCodeFileName; // Retourner le chemin relatif
//     }
// }


// namespace App\Services;

// use App\Services\Contracts\IQrCodeService;
// use Endroid\QrCode\QrCode;
// use Endroid\QrCode\Writer\PngWriter;
// use Illuminate\Support\Facades\Storage;

// class QrCodeService implements IQrCodeService
// {
//     public function generateQrCode($phoneNumber)
//     {
//         $qrCode = QrCode::create($phoneNumber)->setSize(300)->setMargin(10);
//         $writer = new PngWriter();
//         $qrCodeFileName = 'qrcodes/' . $phoneNumber . '.png';
//         $filePath = public_path($qrCodeFileName);

//         $result = $writer->write($qrCode);
//         Storage::disk('public')->put($qrCodeFileName, $result->getString());

//         return 'storage/' . $qrCodeFileName;
//     }
// }




