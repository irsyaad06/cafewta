<?php

namespace App\Filament\Exports;

use App\Models\Expense;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PengeluaranExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('date')
                ->label('Tanggal')
                ->formatStateUsing(fn ($state) => \Carbon\Carbon::parse($state)->format('d/m/Y')),
            ExportColumn::make('category.name')
                ->label('Kategori'),
            ExportColumn::make('description')
                ->label('Keterangan'),
            ExportColumn::make('user.name')
                ->label('Diinput Oleh'),
            // To ensure it is treated as a pure number in Excel, we don't format it as a string with 'Rp'.
            // Returning the pure integer/float allows Excel to SUM it automatically.
            ExportColumn::make('amount')
                ->label('Nominal (Rp)'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor pengeluaran Anda telah selesai dan siap diunduh.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Namun, ' . number_format($failedRowsCount) . ' baris gagal diekspor.';
        }

        return $body;
    }
}
