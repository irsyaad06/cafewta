<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Models\Expense;
use App\Models\ExpenseCategory;

class PurchaseObserver
{
    /**
     * Handle the Purchase "saving" event.
     */
    public function saving(Purchase $purchase): void
    {
        // Calculate total_price before saving if items exist
        // Note: When creating from Filament, items might not be attached yet during the 'saving' event.
        // It's better to calculate this in the Filament Resource or after items are saved.
        // For robustness, we will handle it in the updated event if needed, 
        // but Filament Repeater handles saving relationships automatically.
    }

    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase): void
    {
        // Check if status changed from 'dalam proses' to 'selesai'
        if ($purchase->isDirty('status') && $purchase->status === 'selesai' && $purchase->getOriginal('status') === 'dalam proses') {
            $this->processCompletedPurchase($purchase);
        }
    }

    protected function processCompletedPurchase(Purchase $purchase): void
    {
        // 1. Update Raw Material Stock and Supplier
        foreach ($purchase->items as $item) {
            $material = $item->rawMaterial;
            if ($material) {
                $material->stock += $item->quantity;
                $material->supplier_id = $purchase->supplier_id;
                $material->save();
            }
        }

        // 2. Create Expense
        $category = ExpenseCategory::firstOrCreate(
            ['name' => 'Pembelian Bahan Baku'],
            ['description' => 'Kategori otomatis untuk pembelian bahan baku']
        );

        Expense::create([
            'expense_category_id' => $category->id,
            'user_id' => $purchase->user_id ?? auth()->id(),
            'amount' => $purchase->total_price,
            'description' => 'Pembelian bahan baku ke ' . ($purchase->supplier->name ?? 'Pemasok') . ' (Pesanan #' . $purchase->id . ')',
            'date' => $purchase->date,
        ]);
    }
}
