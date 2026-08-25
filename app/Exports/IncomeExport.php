<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeExport implements FromView, ShouldAutoSize, WithStyles
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
        $query = Transaction::query()->with(['paymentMethod', 'user'])
            ->whereIn('status', ['completed', 'delivered']);

        if ($this->fromDate) {
            $query->whereDate('created_at', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('created_at', '<=', $this->toDate);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $topMenusQuery = \Illuminate\Support\Facades\DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->whereIn('transactions.status', ['completed', 'delivered']);

        if ($this->fromDate) {
            $topMenusQuery->whereDate('transactions.created_at', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $topMenusQuery->whereDate('transactions.created_at', '<=', $this->toDate);
        }

        $topMenus = $topMenusQuery->select('transaction_details.menu_name', \Illuminate\Support\Facades\DB::raw('SUM(transaction_details.quantity) as total_qty'))
            ->groupBy('transaction_details.menu_name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        return view('exports.income', [
            'transactions' => $transactions,
            'topMenus' => $topMenus,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
