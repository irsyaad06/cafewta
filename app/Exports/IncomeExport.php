<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
        $query = Transaction::with(['paymentMethod', 'user'])
            ->whereIn('status', ['completed', 'delivered']);

        if ($this->fromDate) {
            $query->whereDate('created_at', '>=', $this->fromDate);
        }
        if ($this->toDate) {
            $query->whereDate('created_at', '<=', $this->toDate);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'No Invoice',
            'Kasir / User',
            'Metode Pembayaran',
            'Total Tagihan (Rp)',
            'Total HPP (Rp)',
            'Keuntungan (Rp)',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->created_at->format('d/m/Y H:i'),
            $transaction->invoice_number,
            $transaction->user ? $transaction->user->name : '-',
            $transaction->paymentMethod ? $transaction->paymentMethod->name : '-',
            $transaction->total_amount,
            $transaction->total_hpp,
            $transaction->total_profit,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
