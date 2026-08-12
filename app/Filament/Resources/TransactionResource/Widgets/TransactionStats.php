<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\TransactionResource\Pages\ListTransactions;

class TransactionStats extends BaseWidget
{
    use InteractsWithPageTable;

    public array $activeFilters = [];

    #[\Livewire\Attributes\On('updateWidgetFilters')]
    public function updateFilters($filters)
    {
        $this->activeFilters = $filters ?? [];
    }

    protected function getTablePage(): string
    {
        return ListTransactions::class;
    }

    protected function getStats(): array
    {
        $paidStatuses = ['completed', 'cooking', 'delivered'];
        
        // Buat query dasar
        $query = Transaction::query();
        
        $filters = $this->activeFilters;
        $dateFrom  = data_get($filters, 'rentang_tanggal.date_from');
        $dateUntil = data_get($filters, 'rentang_tanggal.date_until');

        // Jika tidak ada filter yang aktif, gunakan default bulan ini
        if (empty($filters)) {
            $dateFrom  = now()->startOfMonth()->format('Y-m-d');
            $dateUntil = now()->endOfMonth()->format('Y-m-d');
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateUntil) {
            $query->whereDate('created_at', '<=', $dateUntil);
        }

        $query->whereIn('status', $paidStatuses);
        
        $totalIncome       = (clone $query)->sum('total_amount');
        $totalProfit       = (clone $query)->sum('total_profit');
        $totalTransactions = (clone $query)->count();

        // Buat label periode untuk judul card
        $fromCarbon  = \Carbon\Carbon::parse($dateFrom);
        $untilCarbon = \Carbon\Carbon::parse($dateUntil);

        if ($fromCarbon->format('Y-m') === $untilCarbon->format('Y-m')) {
            // Bulan yang sama → tampilkan nama bulan + tahun
            $periodLabel = $fromCarbon->translatedFormat('F Y');
        } else {
            // Beda bulan → tampilkan range singkat
            $periodLabel = $fromCarbon->translatedFormat('M Y') . ' – ' . $untilCarbon->translatedFormat('M Y');
        }

        return [
            Stat::make('Total Pemasukan ' . $periodLabel, 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                ->description('Total pemasukan kotor')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Total Keuntungan ' . $periodLabel, 'Rp ' . number_format($totalProfit, 0, ',', '.'))
                ->description('Pemasukan dikurangi HPP')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Total Transaksi ' . $periodLabel, $totalTransactions)
                ->description('Jumlah transaksi sukses')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
        ];
    }
}
