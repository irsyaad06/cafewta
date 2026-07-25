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
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('to_date')
                    ->label('Sampai Tanggal')
                    ->required(),
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
        return \Filament\Actions\Action::make('exportPdf')
            ->label('Export PDF')
            ->color('danger')
            ->form([
                \Filament\Forms\Components\DatePicker::make('from_date')
                    ->label('Dari Tanggal')
                    ->required(),
                \Filament\Forms\Components\DatePicker::make('to_date')
                    ->label('Sampai Tanggal')
                    ->required(),
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

                $expenses = \App\Models\Expense::with(['category', 'user'])
                    ->whereDate('date', '>=', $data['from_date'])
                    ->whereDate('date', '<=', $data['to_date'])
                    ->orderBy('date', 'desc')
                    ->get();

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.pengeluaran', [
                    'expenses' => $expenses,
                    'fromDate' => \Carbon\Carbon::parse($data['from_date'])->format('d/m/Y'),
                    'toDate' => \Carbon\Carbon::parse($data['to_date'])->format('d/m/Y'),
                ]);

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, 'Pengeluaran_' . $data['from_date'] . '_sampai_' . $data['to_date'] . '.pdf');
            });
    }

    public function getSubheading(): \Illuminate\Contracts\Support\Htmlable|string|null
    {
        return view('filament.pages.export-button-subheading');
    }
}
