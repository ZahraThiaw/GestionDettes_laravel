<?php

namespace App\Repositories;

use App\Models\Client;

interface ClientRepositoryInterface
{
    public function all($filters = [], $withUser = false);

    public function find($id, $withUser = false);

    //public function findByTelephone($telephones);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);
    public function registerClientForExistingClient(array $userData, $clientId);
    public function findByTelephone($telephone);
    public function generateLoyaltyCard(Client $client);
    public function generateClientPdf(Client $client);
}
