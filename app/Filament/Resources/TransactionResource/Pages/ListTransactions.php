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
        $filters  = $this->tableFilters['rentang_tanggal'] ?? [];
        $fromDate = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
        $toDate   = $filters['date_until'] ?? now()->endOfMonth()->toDateString();

        $fromLabel = \Carbon\Carbon::parse($fromDate)->translatedFormat('d F Y');
        $toLabel   = \Carbon\Carbon::parse($toDate)->translatedFormat('d F Y');

        return \Filament\Actions\Action::make('exportPdf')
            ->label('Export PDF')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Export PDF')
            ->modalDescription("Anda akan mengunduh PDF Pemasukan dengan data dari tanggal {$fromLabel} hingga {$toLabel}.")
            ->modalSubmitActionLabel('Ya, Download PDF')
            ->modalCancelActionLabel('Batal')
            ->action(function () {
                $filters  = $this->tableFilters['rentang_tanggal'] ?? [];
                $fromDate = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
                $toDate   = $filters['date_until'] ?? now()->endOfMonth()->toDateString();

                $transactions = \App\Models\Transaction::with(['paymentMethod', 'user'])
                    ->whereIn('status', ['completed', 'delivered'])
                    ->whereDate('created_at', '>=', $fromDate)
                    ->whereDate('created_at', '<=', $toDate)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $topMenus = \Illuminate\Support\Facades\DB::table('transaction_details')
                    ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
                    ->whereIn('transactions.status', ['completed', 'delivered'])
                    ->whereDate('transactions.created_at', '>=', $fromDate)
                    ->whereDate('transactions.created_at', '<=', $toDate)
                    ->select('transaction_details.menu_name', \Illuminate\Support\Facades\DB::raw('SUM(transaction_details.quantity) as total_qty'))
                    ->groupBy('transaction_details.menu_name')
                    ->orderByDesc('total_qty')
                    ->limit(10)
                    ->get();

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pemasukan', [
                    'transactions' => $transactions,
                    'topMenus'     => $topMenus,
                    'fromDate'     => \Carbon\Carbon::parse($fromDate)->format('d/m/Y'),
                    'toDate'       => \Carbon\Carbon::parse($toDate)->format('d/m/Y'),
                ])->setPaper('a4', 'landscape');

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'Pemasukan_' . $fromDate . '_sampai_' . $toDate . '.pdf');
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

    public function getWidgetData(): array
    {
        $data = parent::getWidgetData();
        $data['tableColumnSearches'] = $data['tableColumnSearches'] ?? [];
        return $data;
    }

    public function updatedTableFilters(): void
    {
        if (method_exists(parent::class, 'updatedTableFilters')) {
            parent::updatedTableFilters();
        }
        $this->dispatch('updateWidgetFilters', filters: $this->tableFilters);
    }
}
