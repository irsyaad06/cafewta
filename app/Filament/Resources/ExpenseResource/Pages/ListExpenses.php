<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
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
                $count = \App\Models\Expense::whereDate('date', '>=', $data['from_date'])
                    ->whereDate('date', '<=', $data['to_date'])
                    ->count();

                if ($count === 0) {
                    \Filament\Notifications\Notification::make()
                        ->title('Tidak Ada Data')
                        ->body('Tidak ada pengeluaran pada rentang tanggal tersebut.')
                        ->warning()
                        ->send();
                    return;
                }

                return \Maatwebsite\Excel\Facades\Excel::download(
                    new \App\Exports\ExpenseExport($data['from_date'], $data['to_date']),
                    'Pengeluaran_' . $data['from_date'] . '_sampai_' . $data['to_date'] . '.xlsx'
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
            ->modalDescription("Anda akan mengunduh PDF Pengeluaran dengan data dari tanggal {$fromLabel} hingga {$toLabel}.")
            ->modalSubmitActionLabel('Ya, Download PDF')
            ->modalCancelActionLabel('Batal')
            ->action(function () {
                $filters  = $this->tableFilters['rentang_tanggal'] ?? [];
                $fromDate = $filters['date_from'] ?? now()->startOfMonth()->toDateString();
                $toDate   = $filters['date_until'] ?? now()->endOfMonth()->toDateString();

                $expenses = \App\Models\Expense::with(['category', 'user'])
                    ->whereDate('date', '>=', $fromDate)
                    ->whereDate('date', '<=', $toDate)
                    ->orderBy('date', 'desc')
                    ->get();

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pengeluaran', [
                    'expenses'  => $expenses,
                    'fromDate'  => \Carbon\Carbon::parse($fromDate)->format('d/m/Y'),
                    'toDate'    => \Carbon\Carbon::parse($toDate)->format('d/m/Y'),
                ])->setPaper('a4', 'landscape');

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'Pengeluaran_' . $fromDate . '_sampai_' . $toDate . '.pdf');
            });
    }

    public function getSubheading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return view('filament.pages.export-button-subheading');
    }
}
