<?php

namespace App\Services;

use App\Models\Loan;
use Illuminate\Support\Facades\Auth;

class LoanService
{
    public function hasPendingLoan($userId)
    {
        return Loan::where('user_id', $userId)->where('status', 'pending')->exists();
    }

    public function createLoan($userId, array $data, $exchangeRate = null)
    {
        $loan = new Loan();
        $loan->user_id = $userId;
        $loan->amount = $data['amount'];
        $loan->duration = $data['duration'];
        $loan->loan_type = $data['loan_type'];
        $loan->status = 'pending';

        if ($data['loan_type'] === 'crypto' && $exchangeRate) {
            $loan->crypto_currency = $data['crypto_currency'];
            $loan->exchange_rate = $exchangeRate;
        }

        $loan->save();
    }
}
