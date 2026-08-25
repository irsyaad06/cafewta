<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Menu;
use App\Models\PaymentMethod;
use App\Models\User;
use Carbon\Carbon;

class TransactionTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::all();
        if ($menus->count() < 10) {
            $this->command->warn('Jumlah menu kurang dari 10. Seeder mungkin tidak menampilkan Top 10 secara penuh.');
        }

        $paymentMethod = PaymentMethod::first();
        $user = User::where('role', 'admin')->orWhere('role', 'cashier')->first();

        // Buat 30 transaksi dalam rentang 30 hari terakhir
        for ($i = 1; $i <= 30; $i++) {
            $date = Carbon::now()->subDays(rand(1, 30));
            
            $transaction = Transaction::create([
                'invoice_number' => 'INV-TEST-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id ?? 1,
                'payment_method_id' => $paymentMethod->id ?? null,
                'total_amount' => 0,
                'total_hpp' => 0,
                'total_profit' => 0,
                'amount_paid' => 0,
                'change_amount' => 0,
                'status' => 'completed',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $totalAmount = 0;
            $totalHpp = 0;
            
            // Pilih 2-4 menu random untuk setiap transaksi
            // Hanya pilih dari menu yang ada
            if ($menus->count() > 0) {
                $take = min(rand(2, 4), $menus->count());
                $selectedMenus = $menus->random($take);
                
                foreach ($selectedMenus as $menu) {
                    // Beri bobot agar ada menu yang sangat sering/banyak dibeli
                    $qty = rand(1, 3);
                    if ($menu->id % 2 == 0) {
                        $qty += rand(2, 5); // Menu id genap akan lebih banyak dibeli
                    }
                    if ($menu->id == $menus->first()->id) {
                        $qty += 15; // Menu pertama pasti akan jadi no 1
                    }

                    $subtotal = $menu->selling_price * $qty;
                    $subhpp = $menu->hpp * $qty;

                    TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'menu_id' => $menu->id,
                        'menu_name' => $menu->name,
                        'price' => $menu->selling_price,
                        'hpp' => $menu->hpp,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                        'status' => 'delivered',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);

                    $totalAmount += $subtotal;
                    $totalHpp += $subhpp;
                }

                $transaction->update([
                    'total_amount' => $totalAmount,
                    'total_hpp' => $totalHpp,
                    'total_profit' => $totalAmount - $totalHpp,
                    'amount_paid' => $totalAmount,
                ]);
            }
        }

        $this->command->info('Transaction Test Seeder berhasil dijalankan. Data Dummy telah dibuat.');
    }
}
