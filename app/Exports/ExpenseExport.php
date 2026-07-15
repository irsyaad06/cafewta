<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $fromDate;
    protected $toDate;

    public function __construct($fromDate = null, $toDate = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        $query = Expense::with(['category', 'user']);

        if ($this->fromDate) {
            $query->whereDate('date', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('date', '<=', $this->toDate);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Kategori',
            'Keterangan',
            'Diinput Oleh',
            'Nominal (Rp)',
        ];
    }

    public function map($expense): array
    {
        return [
            \Carbon\Carbon::parse($expense->date)->format('d/m/Y'),
            $expense->category ? $expense->category->name : '-',
            $expense->description,
            $expense->user ? $expense->user->name : '-',
            $expense->amount,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
