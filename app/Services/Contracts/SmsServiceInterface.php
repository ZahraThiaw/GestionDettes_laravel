<?php

namespace App\Services\Contracts;

interface SmsServiceInterface
{
    /**
     * Envoyer des rappels de dettes à tous les clients ayant des dettes impayées.
     *
     * @return void
     */
    public function sendDebtReminderToClients();

    /**
     * Envoyer un SMS à un client spécifique.
     *
     * @param string $toPhoneNumber Le numéro de téléphone du client
     * @param float $montantRestant Le montant restant à payer
     * @param string $clientName Le nom du client
     * @return void
     */
    public function sendSmsToClient($toPhoneNumber, $montantRestant, $clientName);
}
