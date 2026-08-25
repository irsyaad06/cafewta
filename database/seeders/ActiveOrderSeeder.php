<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Menu;
use App\Models\User;
use App\Models\CafeTable;
use Carbon\Carbon;

class ActiveOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $menus = Menu::all();
        if ($menus->count() < 3) {
            $this->command->warn('Jumlah menu kurang dari 3. Seeder aktif mungkin gagal.');
            return;
        }

        $user = User::where('role', 'admin')->orWhere('role', 'cashier')->first();
        $tables = CafeTable::all();
        
        $statuses = [
            ['header' => 'pending', 'detail' => 'pending'],
            ['header' => 'pending', 'detail' => 'pending'], // Dua yang pending (butuh pembayaran)
            ['header' => 'completed', 'detail' => 'pending'], // Lunas, masuk dapur (pending dimasak)
            ['header' => 'cooking', 'detail' => 'cooking'], // Sedang dimasak
            ['header' => 'cooking', 'detail' => 'mixed_cooking_ready'], // Sebagian dimasak, sebagian ready
            ['header' => 'ready', 'detail' => 'ready'], // Semua siap diantar
        ];

        foreach ($statuses as $index => $statusSet) {
            $date = Carbon::now()->subMinutes(rand(5, 60));
            $isPaid = in_array($statusSet['header'], ['completed', 'cooking', 'ready', 'delivered']);
            
            // Pilih meja (jika tersedia)
            $tableId = null;
            if ($tables->count() > 0) {
                $tableId = $tables->random()->id;
                CafeTable::where('id', $tableId)->update(['status' => 'occupied']);
            }

            $transaction = Transaction::create([
                'invoice_number' => 'INV-ACT-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT) . $index,
                'user_id' => $user->id ?? 1,
                'payment_method_id' => $isPaid ? 1 : null,
                'cafe_table_id' => $tableId,
                'total_amount' => 0,
                'total_hpp' => 0,
                'total_profit' => 0,
                'amount_paid' => 0,
                'change_amount' => 0,
                'status' => $statusSet['header'],
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $totalAmount = 0;
            $totalHpp = 0;
            
            // Pilih 2-4 menu random
            $take = min(rand(2, 4), $menus->count());
            $selectedMenus = $menus->random($take);
            
            $detailIndex = 0;
            foreach ($selectedMenus as $menu) {
                $qty = rand(1, 2);
                $subtotal = $menu->selling_price * $qty;
                $subhpp = $menu->hpp * $qty;

                // Tentukan detail status
                $detailStatus = 'pending';
                if ($statusSet['detail'] === 'mixed_cooking_ready') {
                    // Beberapa item ready, beberapa cooking
                    $detailStatus = $detailIndex % 2 === 0 ? 'ready' : 'cooking';
                } elseif ($statusSet['detail'] !== 'pending') {
                    $detailStatus = $statusSet['detail'];
                }

                TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'menu_id' => $menu->id,
                    'menu_name' => $menu->name,
                    'price' => $menu->selling_price,
                    'hpp' => $menu->hpp,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'status' => $detailStatus,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $totalAmount += $subtotal;
                $totalHpp += $subhpp;
                $detailIndex++;
            }

            $transaction->update([
                'total_amount' => $totalAmount,
                'total_hpp' => $totalHpp,
                'total_profit' => $totalAmount - $totalHpp,
                'amount_paid' => $isPaid ? $totalAmount : 0,
            ]);
        }

        $this->command->info('Active Order Seeder berhasil dijalankan. Data pesanan aktif telah dibuat.');
    }
}
