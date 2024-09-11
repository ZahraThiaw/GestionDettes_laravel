<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Jobs\SendDebtSummarySmsJob;

class SendDebtSummarySmsCommand extends Command
{
    /**
     * Le nom et la signature de la commande console.
     *
     * @var string
     */
    protected $signature = 'sms:send-debt-summary';

    /**
     * La description de la commande console.
     *
     * @var string
     */
    protected $description = 'Envoie un résumé des dettes restantes par SMS à tous les clients.';

    /**
     * Exécute la commande console.
     *
     * @return int
     */
    public function handle()
    {
        // Récupérer tous les clients ayant des dettes
        $clients = Client::has('dettes')->get();

        // Parcourir chaque client et dispatcher un job pour chaque envoi de SMS
        foreach ($clients as $client) {
            //SendDebtSummarySmsJob::dispatch($client);
        }

        $this->info('Les SMS ont été envoyés à tous les clients avec des dettes.');
        return 0;
    }
}
