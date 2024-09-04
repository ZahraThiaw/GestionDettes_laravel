<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ClientLoyaltyCardMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailTestController extends Controller
{
    public function sendTestEmail(Request $request)
    {
        $client = (object)[
            'name' => 'Fatimata Thiaw',
            'email' => 'fatimatathiaw6@gmail.com',
        ];

        $loyaltyCard = (object)[
            'code' => 'LOYAL1234',
        ];

        Mail::to($client->email)->send(new ClientLoyaltyCardMail($client, $loyaltyCard));

        return response()->json(['message' => 'Email envoyé avec succès']);
    }
}
