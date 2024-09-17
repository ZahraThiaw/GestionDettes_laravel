<?php

namespace App\Http\Controllers;

use App\Services\ArchiveDette;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    protected $debtArchivingService;

    public function __construct(ArchiveDette $debtArchivingService)
    {
        $this->debtArchivingService = $debtArchivingService;
    }


    // Get all archived debts
    public function getArchivedDebts()
    {
        try {
            $archivedDebts = $this->debtArchivingService->getArchivedDebts();
            return [
                'statut' => 'Success',
                'data' => $archivedDebts,
                'message' => 'Liste des dettes archivées récupérée avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return[
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Impossible de récupérer les dettes archivées.',
                'httpStatus' => 500
            ];
        }
    }

    // Get archived debts by client ID
    public function getArchivedDebtsByClient($clientId)
    {
        try {
            $clientDebts = $this->debtArchivingService->getArchivedDebtsByClient($clientId);
            return [
                'statut' => 'Success',
                'data' => $clientDebts,
                'message' => 'Dettes du client récupérées avec succès.',
                'httpStatus' => 200
            ];
        } catch (\Exception $e) {
            return [
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Impossible de récupérer les dettes du client.',
                'httpStatus' => 500
            ];
        }
    }

    // Get a specific archived debt by ID
    public function getArchivedDebtById($debtId)
    {
        try {
            $debt = $this->debtArchivingService->getArchivedDebtById($debtId);
            return response()->json([
                'statut' => 'Success',
                'data' => $debt,
                'message' => 'Dette récupérée avec succès.',
                'httpStatus' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Impossible de récupérer cette dette.',
                'httpStatus' => 500
            ], 500);
        }
    }

    // Restore debts archived on a specific date
    public function restoreArchivedDebtsByDate($date)
    {
        try {
            $success = $this->debtArchivingService->restoreArchivedDebtsByDate($date);
            if ($success) {
                
                return response()->json([
                    'statut' => 'Success',
                    'data' => [],
                    'message' => 'Dettes archivées restaurées avec succès.',
                    'httpStatus' => 200
                ], 200);
            } else {
                return response()->json([
                    'statut' => 'Echec',
                    'data' => [],
                    'message' => 'Aucune dette archivée pour cette date.',
                    'httpStatus' => 404
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Erreur lors de la restauration des dettes archivées.',
                'httpStatus' => 500
            ], 500);
        }
    }

    // Restore a specific archived debt
    public function restoreArchivedDebt($debtId)
    {
        try {
            $success = $this->debtArchivingService->restoreArchivedDebt($debtId);
            if ($success) {
                return response()->json([
                    'statut' => 'Success',
                    'data' => [],
                    'message' => 'Dette restaurée avec succès.',
                    'httpStatus' => 200
                ], 200);
            } else {
                return response()->json([
                    'statut' => 'Echec',
                    'data' => [],
                    'message' => 'Cette dette n\'existe pas ou est déjà restaurée.',
                    'httpStatus' => 404
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Erreur lors de la restauration de la dette.',
                'httpStatus' => 500
            ], 500);
        }
    }

    // Restore archived debts by client ID
    public function restoreArchivedDebtsByClient($clientId)
    {
        try {
            $success = $this->debtArchivingService->restoreArchivedDebtsByClient($clientId);
            if ($success) {
                return response()->json([
                    'statut' => 'Success',
                    'data' => [],
                    'message' => 'Dettes du client restaurées avec succès.',
                    'httpStatus' => 200
                ], 200);
            } else {
                return response()->json([
                    'statut' => 'Echec',
                    'data' => [],
                    'message' => 'Les dettes du client n\'existent pas ou sont déjà restaurées.',
                    'httpStatus' => 404
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'statut' => 'Echec',
                'data' => [],
                'message' => 'Erreur lors de la restauration des dettes du client.',
                'httpStatus' => 500
            ], 500);
        }
    }
}
