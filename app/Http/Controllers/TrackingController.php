<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrackingController extends Controller
{
    public function show($invoice_number)
    {
        $transaction = Transaction::with(['transactionDetails', 'cafeTable', 'paymentMethod'])
            ->where('invoice_number', $invoice_number)
            ->firstOrFail();

        return Inertia::render('Tracking/Show', [
            'transaction' => $transaction
        ]);
    }
}
