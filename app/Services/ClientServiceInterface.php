<?php

namespace App\Services;

interface ClientServiceInterface
{
    public function getAllClients($filters = []);

    public function getClientById($id, $withUser = false);

    //public function getClientsByTelephone($telephone);

    public function createClient($data);

    public function updateClient($id, $data);

    public function deleteClient($id);
    public function registerClientForExistingClient(array $userData, $clientId);
    public function getClientByTelephone($telephone);

}
