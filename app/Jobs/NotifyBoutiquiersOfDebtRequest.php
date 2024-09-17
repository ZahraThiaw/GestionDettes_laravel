<?php

namespace App\Jobs;

use App\Models\Demande;
use App\Notifications\DebtRequestSubmittedNotification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyBoutiquiersOfDebtRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $demande;

    public function __construct(Demande $demande)
    {
        $this->demande = $demande;
    }

    public function handle()
    {
        $boutiquiers = User::whereHas('role', function ($query) {
            $query->where('name', 'Boutiquier');
        })->get();

        foreach ($boutiquiers as $boutiquier) {
            $boutiquier->notify(new DebtRequestSubmittedNotification($this->demande));
        }
    }
}
