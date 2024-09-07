<?php

namespace App\Services;

use App\Models\Client;
use App\Models\User;
use App\Repositories\ClientRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\WriterInterface;
use App\Mail\ClientLoyaltyCardMail;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Jobs\StoreImageInCloud;

class ClientService implements ClientServiceInterface
{
    protected $clientRepository;

    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function getAllClients($filters = [])
    {
        return $this->clientRepository->all($filters, isset($filters['include']) && $filters['include'] === 'user');
    }

    public function getClientById($id, $withUser = false)
    {
        return $this->clientRepository->find($id, $withUser);
    }

    public function getClientsByTelephone($telephone)
    {
        $telephones = explode(',', $telephone);
        return $this->clientRepository->findByTelephone($telephones);
    }

    public function createClient($id)
    {
        $this->clientRepository->create($id);
    }
    public function updateClient($id, $data)
    {
        $client = $this->clientRepository->update($id, $data['client']);

        if (isset($data['user'])) {
            if ($client->user_id) {
                $user = User::find($client->user_id);
                $user->update($data['user']);
            } else {
                $user = User::create(array_merge($data['user'], ['role' => 'Client']));
                $client->user_id = $user->id;
                $client->save();
            }
        }

        return $client;
    }

    public function deleteClient($id)
    {
        $this->clientRepository->delete($id);
    }

    public function registerClientForExistingClient(array $userData, $clientId)
    {
        return $this->clientRepository->registerClientForExistingClient($userData, $clientId);
    }

    public function getClientByTelephone($telephone)
    {
        return $this->clientRepository->findByTelephone($telephone);
    }
}
