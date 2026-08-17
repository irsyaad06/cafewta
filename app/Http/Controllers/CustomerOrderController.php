<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Menu;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Enums\PaymentMethodType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CustomerOrderController extends Controller
{
    public function index($table_number)
    {
        $table = CafeTable::where('table_number', $table_number)
            ->where('status', 'available')
            ->first();

        if (!$table) {
            return abort(404, 'Meja tidak ditemukan atau sedang tidak tersedia.');
        }

        $menus = Menu::with('category')->where('is_available', true)->get();
        $categories = Category::all();
        $paymentMethods = PaymentMethod::all();

        return Inertia::render('CustomerOrder/Index', [
            'cafeTable' => $table,
            'menus' => $menus,
            'categories' => $categories,
            'paymentMethods' => $paymentMethods,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cafe_table_id' => 'required|exists:cafe_tables,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'cart' => 'required|array|min:1',
            'cart.*.menu_id' => 'required|exists:menus,id',
            'cart.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $subtotal = 0;
            $totalHpp = 0;
            $taxRate = 0;
            $discount = 0;

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
            $totalProfit = ($subtotal - $discount) - $totalHpp;

            // Cek apakah metode pembayaran adalah QRIS
            $paymentMethod = PaymentMethod::find($validated['payment_method_id']);
            $isQris = $paymentMethod && $paymentMethod->type === PaymentMethodType::Qris;

            $qrisToken = null;
            $status = 'pending';

            if ($isQris) {
                $qrisToken = Str::uuid()->toString();
                $status = 'pending_qris';
            }

            $transaction = Transaction::create([
                'invoice_number' => 'INV-QR-' . time() . '-' . rand(1000, 9999),
                'qris_token' => $qrisToken,
                'user_id' => null, // Pelanggan tidak login
                'cafe_table_id' => $validated['cafe_table_id'],
                'payment_method_id' => $validated['payment_method_id'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discount,
                'total_amount' => $totalAmount,
                'total_hpp' => $totalHpp,
                'total_profit' => $totalProfit,
                'amount_paid' => 0,
                'change_amount' => 0,
                'status' => $status,
            ]);

            foreach ($transactionDetailsData as $detailData) {
                $transaction->transactionDetails()->create($detailData);
            }

            // Ubah status meja menjadi occupied agar tidak bisa di-scan ulang oleh pengunjung lain
            $table = CafeTable::find($validated['cafe_table_id']);
            $table->update(['status' => 'occupied']);

            DB::commit();

            return redirect()->route('order.success', [
                'table_number' => $table->table_number, 
                'transaction' => $transaction->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function success($table_number, Transaction $transaction)
    {
        $transaction->load(['transactionDetails', 'cafeTable', 'paymentMethod']);
        
        return Inertia::render('CustomerOrder/Success', [
            'transaction' => $transaction,
            'table_number' => $table_number,
        ]);
    }
}
