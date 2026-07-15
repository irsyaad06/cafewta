<?php

namespace App\Http\Controllers;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Menu;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PosController extends Controller
{
    public function index()
    {
        $menus = Menu::with('category')->where('is_available', true)->get();
        $categories = Category::all();
        $cafeTables = CafeTable::where('status', 'available')->get();
        $paymentMethods = PaymentMethod::all();
        $pendingOrdersCount = Transaction::where('status', 'pending')->count();

        return Inertia::render('Pos/Index', [
            'menus' => $menus,
            'categories' => $categories,
            'cafeTables' => $cafeTables,
            'paymentMethods' => $paymentMethods,
            'pendingOrdersCount' => $pendingOrdersCount,
        ]);
    }

    public function orders(Request $request)
    {
        $query = Transaction::with(['cafeTable', 'transactionDetails']);

        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        $period = $request->input('period', 'today');

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'yesterday') {
            $query->whereDate('created_at', today()->subDay());
        } elseif ($period === 'this_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'this_month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Pos/Orders', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'period']),
        ]);
    }

    public function updateOrderStatus(Request $request, Transaction $transaction)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,completed,cooking,ready,delivered',
            'amount_paid' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $originalStatus = $transaction->getOriginal('status');
            $transaction->status = $validated['status'];

            // Cek jika status berubah menjadi 'ready', kurangi stok bahan baku
            if ($validated['status'] === 'ready' && $originalStatus !== 'ready') {
                $transaction->load('transactionDetails.menu.recipes.rawMaterial');
                
                foreach ($transaction->transactionDetails as $detail) {
                    if ($detail->menu && $detail->menu->recipes) {
                        foreach ($detail->menu->recipes as $recipe) {
                            $rawMaterial = $recipe->rawMaterial;
                            if ($rawMaterial) {
                                // Kurangi stok: kuantitas resep * jumlah porsi yang dipesan
                                $rawMaterial->stock -= ($recipe->quantity * $detail->quantity);
                                $rawMaterial->save();
                            }
                        }
                    }
                }
            }

            // Cek pembayaran jika status diubah ke completed
            if ($validated['status'] === 'completed') {
                // Pastikan jika amount_paid dikirim, ia memenuhi total_amount
                if ($request->has('amount_paid') && $request->amount_paid >= $transaction->total_amount) {
                    $transaction->amount_paid = $validated['amount_paid'];
                    $transaction->change_amount = $validated['amount_paid'] - $transaction->total_amount;
                } else if ($transaction->amount_paid < $transaction->total_amount) {
                    throw new \Exception("Jumlah pembayaran kurang dari total tagihan.");
                }
            }

            // Kembalikan status meja ke tersedia ketika pesanan sudah diantarkan
            if ($validated['status'] === 'delivered' && $transaction->cafe_table_id) {
                $table = CafeTable::find($transaction->cafe_table_id);
                if ($table) {
                    $table->update(['status' => 'available']);
                }
            }

            $transaction->save();
            DB::commit();

            return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cafe_table_id' => 'nullable|exists:cafe_tables,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'amount_paid' => 'required|numeric|min:0',
            'cart' => 'required|array|min:1',
            'cart.*.menu_id' => 'required|exists:menus,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $totalHpp = 0;
            $taxRate = 0; // Tax removed as requested
            $discount = 0;

            // Calculate Subtotal & HPP
            $transactionDetailsData = [];
            foreach ($validated['cart'] as $item) {
                $menu = Menu::find($item['menu_id']);
                $itemSubtotal = $menu->selling_price * $item['quantity'];
                $itemTotalHpp = $menu->hpp * $item['quantity'];
                
                $subtotal += $itemSubtotal;
                $totalHpp += $itemTotalHpp;

                $transactionDetailsData[] = [
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'price' => $menu->selling_price,
                    'hpp' => $menu->hpp,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                ];
            }

            $taxAmount = $subtotal * $taxRate;
            $totalAmount = $subtotal + $taxAmount - $discount;
            $changeAmount = $validated['amount_paid'] - $totalAmount;
            
            // Keuntungan = (Subtotal Penjualan Bersih) - Total HPP 
            // Karena pajak itu untuk pemerintah dan diskon memotong pemasukan.
            // Asumsi sederhana: profit = subtotal - diskon - totalHpp
            $totalProfit = ($subtotal - $discount) - $totalHpp;

            if ($changeAmount < 0) {
                throw new \Exception("Amount paid is less than total amount.");
            }

            // Create Transaction
            $transaction = Transaction::create([
                'invoice_number' => 'INV-' . time() . '-' . rand(1000, 9999),
                'user_id' => auth()->id() ?? 1, // Fallback to 1 if not logged in for testing
                'cafe_table_id' => $validated['cafe_table_id'],
                'payment_method_id' => $validated['payment_method_id'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'total_hpp' => $totalHpp,
                'total_profit' => $totalProfit,
                'amount_paid' => $validated['amount_paid'],
                'change_amount' => $changeAmount,
                'status' => 'completed',
            ]);

            // Create Details
            foreach ($transactionDetailsData as $detailData) {
                $transaction->transactionDetails()->create($detailData);
            }

            // Update Table Status if table selected
            if ($validated['cafe_table_id']) {
                $table = CafeTable::find($validated['cafe_table_id']);
                $table->update(['status' => 'occupied']);
            }

            DB::commit();

            return redirect()->back()
                ->with('success', 'Transaction completed successfully.')
                ->with('invoice_number', $transaction->invoice_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
