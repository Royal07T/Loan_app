<?php

namespace App\Observers;

use App\Models\Loan;
use Illuminate\Support\Facades\Log;

class LoanObserver
{
    /**
     * Handle the Loan "created" event.
     */
    public function created(Loan $loan): void
    {
        Log::info("Loan ID {$loan->id} has been created.");
    }

    /**
     * Handle the Loan "updated" event.
     */
    public function updated(Loan $loan): void
    {
        Log::info("Loan ID {$loan->id} has been updated. New Status: {$loan->status}");
    }

    /**
     * Handle the Loan "deleted" event.
     */
    public function deleted(Loan $loan): void
    {
        Log::info("Loan ID {$loan->id} has been deleted.");
    }
}
