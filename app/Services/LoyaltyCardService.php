<?php
// namespace App\Services;

// use App\Services\Contracts\ILoyaltyCardService;
// use Dompdf\Dompdf;

// class LoyaltyCardService implements ILoyaltyCardService
// {
//     public function generateLoyaltyCard($client)
//     {
//         $dompdf = new Dompdf();
        
//         // Load HTML content
//         $html = view('loyalty_card', ['client' => $client])->render();
//         $dompdf->loadHtml($html);
//         $dompdf->setPaper('A4', 'portrait');
//         $dompdf->render();

//         // Save PDF to a file
//         $filePath = public_path("loyalty_cards/{$client->id}_loyalty_card.pdf");
//         file_put_contents($filePath, $dompdf->output());

//         return $filePath; // Return the path to the loyalty card PDF
//     }
// }



namespace App\Services;

use App\Services\Contracts\ILoyaltyCardService;
use Dompdf\Dompdf;
use App\Models\Client;
use App\Services\Contracts\IQrCodeService;
use Illuminate\Support\Facades\Storage;
use App\Services\QrCodeService;

class LoyaltyCardService implements ILoyaltyCardService
{
    protected $qrCodeService;

    public function __construct(IQrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function generateLoyaltyCard(Client $client)
    {
        // Générer le QR code pour le téléphone du client et obtenir le chemin du fichier
        $qrCodePath = $this->qrCodeService->generateQrCode($client->telephone);

        // Mettre à jour le champ 'qrcode' dans la table client
        $client->update(['qrcode' => $qrCodePath]);

        // Charger le contenu de la vue Blade pour générer le PDF
        $html = view('pdf.loyalty_card', [
            'client' => $client,
            'qrCodePath' => asset($qrCodePath), // Utiliser asset pour générer le chemin complet du QR code
            'clientPhoto' => asset('storage/' . $client->user->photo) // Chemin complet pour la photo du client
        ])->render();

        // Initialiser Dompdf pour générer le PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Sauvegarder le fichier PDF
        $pdfFilePath = public_path("loyalty_cards/{$client->id}_loyalty_card.pdf");
        file_put_contents($pdfFilePath, $dompdf->output());

        return $pdfFilePath; // Retourner le chemin du fichier PDF généré
    }
}
