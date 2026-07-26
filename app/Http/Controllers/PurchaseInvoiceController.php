<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class PurchaseInvoiceController extends Controller
{
    public function show(Purchase $purchase)
    {
        $purchase->load(['items.rawMaterial', 'supplier', 'user']);
        
        return view('purchases.invoice', compact('purchase'));
    }
}
