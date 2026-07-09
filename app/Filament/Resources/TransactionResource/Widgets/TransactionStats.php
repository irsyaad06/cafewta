<?php

namespace App\Filament\Resources\TransactionResource\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TransactionStats extends BaseWidget
{
    protected function getStats(): array
    {
        $paidStatuses = ['completed', 'cooking', 'delivered'];
        
        $totalIncome = Transaction::whereIn('status', $paidStatuses)->sum('total_amount');
        $totalProfit = Transaction::whereIn('status', $paidStatuses)->sum('total_profit');
        $totalTransactions = Transaction::whereIn('status', $paidStatuses)->count();

        return [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalIncome, 0, ',', '.'))
                ->description('Total pemasukan kotor')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Total Keuntungan', 'Rp ' . number_format($totalProfit, 0, ',', '.'))
                ->description('Pemasukan dikurangi HPP')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Total Transaksi', $totalTransactions)
                ->description('Jumlah transaksi sukses')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),
        ];
    }
}
