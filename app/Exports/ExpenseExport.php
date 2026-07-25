<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ExpenseExport implements FromView, ShouldAutoSize
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
        $query = Expense::with(['category', 'user']);

        if ($this->fromDate) {
            $query->whereDate('date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('date', '<=', $this->toDate);
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        return view('exports.expenses', [
            'expenses' => $expenses,
            'fromDate' => $this->fromDate ? \Carbon\Carbon::parse($this->fromDate)->format('d/m/Y') : '-',
            'toDate' => $this->toDate ? \Carbon\Carbon::parse($this->toDate)->format('d/m/Y') : '-',
        ]);
    }
}
