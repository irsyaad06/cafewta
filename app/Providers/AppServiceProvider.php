<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\SelectFilter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Purchase::observe(\App\Observers\PurchaseObserver::class);
        \App\Models\PurchaseItem::observe(\App\Observers\PurchaseItemObserver::class);

        Vite::prefetch(concurrency: 3);

        Select::configureUsing(function (Select $select): void {
            $select->native(false);
        });

        SelectFilter::configureUsing(function (SelectFilter $filter): void {
            $filter->native(false);
        });
    }
}
