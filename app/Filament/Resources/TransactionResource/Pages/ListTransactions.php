<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    public function exportExcelAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('exportExcel')
            ->label('Export Excel')
            ->color('success')
            ->form([
                \Filament\Forms\Components\DatePicker::make('from_date')
                    ->label('Dari Tanggal')
                    ->required()
                    ->default(now()->startOfMonth()),
                \Filament\Forms\Components\DatePicker::make('to_date')
                    ->label('Sampai Tanggal')
                    ->required()
                    ->default(now()->endOfMonth()),
            ])
            ->action(function (array $data) {
                $count = \App\Models\Transaction::whereIn('status', ['completed', 'delivered'])
                    ->whereDate('created_at', '>=', $data['from_date'])
                    ->whereDate('created_at', '<=', $data['to_date'])
                    ->count();

                if ($count === 0) {
                    \Filament\Notifications\Notification::make()
                        ->title('Tidak Ada Data')
                        ->body('Tidak ada pemasukan/transaksi pada rentang tanggal tersebut.')
                        ->warning()
                        ->send();
                    return;
                }

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\IncomeExport($data['from_date'], $data['to_date']),
                    'Pemasukan_' . $data['from_date'] . '_sampai_' . $data['to_date'] . '.xlsx'
                );
            });
    }

    public function exportPdfAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('exportPdf')
            ->label('Export PDF')
            ->color('danger')
            ->form([
                \Filament\Forms\Components\DatePicker::make('from_date')
                    ->label('Dari Tanggal')
                    ->required()
                    ->default(now()->startOfMonth()),
                \Filament\Forms\Components\DatePicker::make('to_date')
                    ->label('Sampai Tanggal')
                    ->required()
                    ->default(now()->endOfMonth()),
            ])
            ->action(function (array $data) {
                $count = \App\Models\Transaction::whereIn('status', ['completed', 'delivered'])
                    ->whereDate('created_at', '>=', $data['from_date'])
                    ->whereDate('created_at', '<=', $data['to_date'])
                    ->count();

                if ($count === 0) {
                    \Filament\Notifications\Notification::make()
                        ->title('Tidak Ada Data')
                        ->body('Tidak ada pemasukan/transaksi pada rentang tanggal tersebut.')
                        ->warning()
                        ->send();
                    return;
                }

                $transactions = \App\Models\Transaction::with(['paymentMethod', 'user'])
                    ->whereIn('status', ['completed', 'delivered'])
                    ->whereDate('created_at', '>=', $data['from_date'])
                    ->whereDate('created_at', '<=', $data['to_date'])
                    ->orderBy('created_at', 'desc')
                    ->get();

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pemasukan', [
                    'transactions' => $transactions,
                    'fromDate' => \Carbon\Carbon::parse($data['from_date'])->format('d/m/Y'),
                    'toDate' => \Carbon\Carbon::parse($data['to_date'])->format('d/m/Y'),
                ]);

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'Pemasukan_' . $data['from_date'] . '_sampai_' . $data['to_date'] . '.pdf');
            });
    }

    public function getSubheading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return view('filament.pages.export-button-subheading');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\TransactionResource\Widgets\TransactionStats::class,
        ];
    }
}
