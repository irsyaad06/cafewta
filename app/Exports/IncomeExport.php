<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class IncomeExport implements FromView, ShouldAutoSize
{
    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function view(): View
    {
        $query = Transaction::with(['paymentMethod', 'user'])
            ->whereIn('status', ['completed', 'delivered']);

        if ($this->fromDate) {
            $query->whereDate('created_at', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('created_at', '<=', $this->toDate);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return view('exports.income', [
            'transactions' => $transactions,
            'fromDate' => $this->fromDate ? \Carbon\Carbon::parse($this->fromDate)->format('d/m/Y') : '-',
            'toDate' => $this->toDate ? \Carbon\Carbon::parse($this->toDate)->format('d/m/Y') : '-',
        ]);
    }
}
