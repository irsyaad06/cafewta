<?php

namespace App\Observers;

use App\Models\PurchaseItem;

class PurchaseItemObserver
{
    public function saved(PurchaseItem $purchaseItem): void
    {
        $this->updatePurchaseTotal($purchaseItem);
    }

    public function deleted(PurchaseItem $purchaseItem): void
    {
        $this->updatePurchaseTotal($purchaseItem);
    }

    protected function updatePurchaseTotal(PurchaseItem $purchaseItem): void
    {
        $purchase = $purchaseItem->purchase;
        if ($purchase) {
            $total = $purchase->items()->sum('subtotal');
            // We use DB::table to avoid triggering the 'updated' event on Purchase again unnecessarily,
            // unless we want it to trigger. We actually only care about 'status' changes for the expense.
            // But doing $purchase->update is fine too. Let's just do it cleanly.
            $purchase->total_price = $total;
            $purchase->saveQuietly();
        }
    }
}
