<?php
// namespace App\Services;

// use App\Services\Contracts\IQrCodeService;
// use Endroid\QrCode\QrCode;
// use Endroid\QrCode\Writer\PngWriter;

// class QrCodeService implements IQrCodeService
// {
//     public function generateQrCode($phoneNumber)
//     {
//         // Créer un QR code
//         $qrCode = QrCode::create($phoneNumber)
//             ->setSize(300)
//             ->setMargin(10);

//         // Utiliser PngWriter pour écrire le QR code dans un fichier
//         $writer = new PngWriter();
//         $filePath = public_path("qrcodes/{$phoneNumber}.png");

//         // Écrire le QR code dans un fichier
//         $result = $writer->write($qrCode);
//         $result->saveToFile($filePath); // Sauvegarder le QR code dans un fichier

//         return $filePath; // Retourner le chemin du fichier QR code
//     }
// }

namespace App\Services;

use App\Services\Contracts\IQrCodeService;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService implements IQrCodeService
{
    public function generateQrCode($phoneNumber)
    {
        // Créer un QR code
        $qrCode = QrCode::create($phoneNumber)
            ->setSize(300)
            ->setMargin(10);

        // Utiliser PngWriter pour écrire le QR code dans un fichier
        $writer = new PngWriter();
        $qrCodeFileName = 'qrcodes/' . $phoneNumber . '.png'; // Le fichier sera enregistré ici
        $filePath = public_path($qrCodeFileName);

        // Sauvegarder le QR code dans le stockage public
        $result = $writer->write($qrCode);
        Storage::disk('public')->put($qrCodeFileName, $result->getString());

        return 'storage/' . $qrCodeFileName; // Retourner le chemin relatif du fichier
    }
}
