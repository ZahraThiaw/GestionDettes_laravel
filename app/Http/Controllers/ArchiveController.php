<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FirebaseArchivingService;
use App\Services\DebtArchivingService;
use Symfony\Component\HttpFoundation\Response;

class ArchiveController extends Controller
{
    protected $firebaseService;
    protected $mongoService;

    public function __construct()
    {
        $this->firebaseService = new FirebaseArchivingService();
        $this->mongoService = new DebtArchivingService();
    }

    /**
     * Get all archived debts from both Firebase and MongoDB.
     */
    public function getArchivedDebts()
    {
        $firebaseDebts = $this->firebaseService->getArchivedDebts();
        $mongoDebts = $this->mongoService->getArchivedDebts();

        return response()->json([
            'firebase' => $firebaseDebts,
            'mongo' => $mongoDebts
        ]);
    }

    /**
     * Get archived debts by client ID from both Firebase and MongoDB.
     */
    public function getArchivedDebtsByClient($clientId)
    {
        $firebaseDebts = $this->firebaseService->getArchivedDebtsByClient($clientId);
        $mongoDebts = $this->mongoService->getArchivedDebtsByClient($clientId);

        return response()->json([
            'firebase' => $firebaseDebts,
            'mongo' => $mongoDebts
        ]);
    }

    /**
     * Get a specific archived debt by its ID from both Firebase and MongoDB.
     */
    public function getArchivedDebt($debtId)
    {
        $firebaseDebt = $this->firebaseService->getArchivedDebtById($debtId);
        $mongoDebt = $this->mongoService->getArchivedDebtById($debtId);

        return response()->json([
            'firebase' => $firebaseDebt,
            'mongo' => $mongoDebt
        ]);
    }

    /**
     * Restore a specific archived debt by its ID.
     */
    public function restoreArchivedDebt($debtId)
    {
        // Restore debt in MongoDB
        if ($this->mongoService->restoreArchivedDebt($debtId)) {
            // Restore debt in Firebase
            $this->firebaseService->restoreArchivedDebt($debtId);
            return response()->json(['message' => 'Debt restored successfully.'], Response::HTTP_OK);
        }

        return response()->json(['message' => 'Debt not found.'], Response::HTTP_NOT_FOUND);
    }

    /**
     * Restore archived debts by date.
     */
    public function restoreDebtsByDate($date)
    {
        // Restore debts in MongoDB
        $this->mongoService->restoreArchivedDebtsByDate($date);

        // Restore debts in Firebase
        $this->firebaseService->restoreArchivedDebtsByDate($date);

        return response()->json(['message' => 'Debts restored successfully.'], Response::HTTP_OK);
    }

    /**
     * Restore all archived debts for a client.
     */
    public function restoreDebtsByClient($clientId)
    {
        // Restore debts in MongoDB
        $this->mongoService->restoreArchivedDebtsByClient($clientId);

        // Restore debts in Firebase
        $this->firebaseService->restoreArchivedDebtsByClient($clientId);

        return response()->json(['message' => 'Debts restored successfully.'], Response::HTTP_OK);
    }
}
