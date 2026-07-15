<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\IncomeExport;
use App\Exports\ExpenseExport;
use Illuminate\Support\Carbon;

class FinanceController extends Controller
{
    public function income(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $query = Transaction::with(['paymentMethod', 'user'])
            ->whereIn('status', ['completed', 'delivered']);

        if ($month) {
            $query->whereMonth('created_at', $month);
        }
        if ($year) {
            $query->whereYear('created_at', $year);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Finance/Income', [
            'transactions' => $transactions,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ]
        ]);
    }

    public function expenses(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $query = Expense::with(['category', 'user']);

        if ($month) {
            $query->whereMonth('date', $month);
        }
        if ($year) {
            $query->whereYear('date', $year);
        }

        $expenses = $query->orderBy('date', 'desc')->get();
        $categories = ExpenseCategory::all();

        return Inertia::render('Finance/Expenses', [
            'expenses' => $expenses,
            'categories' => $categories,
            'filters' => [
                'month' => $month,
                'year' => $year,
            ]
        ]);
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'date' => 'required|date',
        ]);

        $validated['user_id'] = auth()->id();

        Expense::create($validated);

        return redirect()->back()->with('success', 'Pengeluaran berhasil ditambahkan.');
    }

    public function exportIncome(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $fileName = 'Pemasukan_' . ($month ?? 'All') . '_' . ($year ?? 'All') . '.xlsx';
        
        return Excel::download(new IncomeExport($month, $year), $fileName);
    }

    public function exportExpenses(Request $request)
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $fileName = 'Pengeluaran_' . ($month ?? 'All') . '_' . ($year ?? 'All') . '.xlsx';
        
        return Excel::download(new ExpenseExport($month, $year), $fileName);
    }
}
