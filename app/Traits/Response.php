<?php

namespace App\Traits;

use App\Enums\StatutResponse;

trait Response
{
    public function sendResponse($data, StatutResponse $statut, $message = '', $httpStatus = 200)
    {
        return response()->json([
            'statut' => $statut->value,
            'data' => $data,
            'message' => $message,
        ], $httpStatus);
    }
}
