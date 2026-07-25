<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FinancialDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada user, kategori pengeluaran, dan metode pembayaran
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create();
        }

        $expenseCategory = ExpenseCategory::first();
        if (!$expenseCategory) {
            $expenseCategory = ExpenseCategory::create([
                'name' => 'Operasional',
            ]);
        }

        $paymentMethod = PaymentMethod::first();
        if (!$paymentMethod) {
            $paymentMethod = PaymentMethod::create([
                'name' => 'Tunai',
            ]);
        }

        $currentYear = date('Y');

        for ($month = 1; $month <= 12; $month++) {
            $daysInMonth = Carbon::create($currentYear, $month)->daysInMonth;
            
            // Seed 5-8 Expenses per month
            $numExpenses = rand(5, 8);
            for ($i = 0; $i < $numExpenses; $i++) {
                $day = rand(1, $daysInMonth);
                $date = Carbon::create($currentYear, $month, $day);
                
                Expense::create([
                    'expense_category_id' => $expenseCategory->id,
                    'user_id' => $user->id,
                    'amount' => rand(50000, 500000),
                    'description' => 'Pengeluaran otomatis (Seeder) ' . $date->format('d M Y'),
                    'date' => $date,
                ]);
            }
            
            // Seed 10-15 Transactions per month
            $numTransactions = rand(10, 15);
            for ($i = 0; $i < $numTransactions; $i++) {
                $day = rand(1, $daysInMonth);
                $date = Carbon::create($currentYear, $month, $day, rand(10, 22), rand(0, 59), rand(0, 59));
                
                $totalHpp = rand(20000, 150000);
                $totalAmount = $totalHpp + rand(20000, 100000); // Pastikan profit positif
                
                // Menggunakan DB::table untuk memastikan created_at sesuai dengan tanggal yang kita tentukan
                DB::table('transactions')->insert([
                    'invoice_number' => 'INV-' . $date->format('YmdHis') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'user_id' => $user->id,
                    'payment_method_id' => $paymentMethod->id,
                    'subtotal' => $totalAmount,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => $totalAmount,
                    'total_hpp' => $totalHpp,
                    'total_profit' => $totalAmount - $totalHpp,
                    'amount_paid' => $totalAmount,
                    'change_amount' => 0,
                    'status' => 'completed',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }
    }
}
