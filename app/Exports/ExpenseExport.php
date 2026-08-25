<?php

namespace App\Exports;

use App\Models\Expense;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseExport implements FromView, ShouldAutoSize, WithStyles
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
        $query = Expense::query()->with(['category', 'user']);

        if ($this->fromDate) {
            $query->whereDate('date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('date', '<=', $this->toDate);
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        $topExpensesQuery = \Illuminate\Support\Facades\DB::table('expenses')
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id');

        if ($this->fromDate) {
            $topExpensesQuery->whereDate('expenses.date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $topExpensesQuery->whereDate('expenses.date', '<=', $this->toDate);
        }

        $topExpenses = $topExpensesQuery->select('expense_categories.name as category_name', \Illuminate\Support\Facades\DB::raw('COUNT(expenses.id) as total_count'))
            ->groupBy('expense_categories.name')
            ->orderByDesc('total_count')
            ->limit(5)
            ->get();

        return view('exports.expenses', [
            'expenses' => $expenses,
            'topExpenses' => $topExpenses,
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
