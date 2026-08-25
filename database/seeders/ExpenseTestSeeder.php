<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Carbon\Carbon;

class ExpenseTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = ExpenseCategory::all();
        if ($categories->count() < 3) {
            $this->command->warn('Kategori pengeluaran kurang dari 3. Seeder mungkin tidak bervariasi.');
        }

        $user = User::where('role', 'admin')->orWhere('role', 'cashier')->first();

        // Buat 50 pengeluaran dalam rentang 30 hari terakhir
        for ($i = 1; $i <= 50; $i++) {
            $date = Carbon::now()->subDays(rand(1, 30));
            
            if ($categories->count() > 0) {
                // Pilih kategori secara acak, berikan bobot untuk membuat kategori pertama paling sering
                $categoryId = null;
                $rand = rand(1, 100);
                
                if ($rand <= 50) { // 50% kesempatan untuk kategori pertama (Paling sering)
                    $categoryId = $categories->first()->id;
                } elseif ($rand <= 80 && $categories->count() > 1) { // 30% untuk kategori kedua
                    $categoryId = $categories[1]->id;
                } else {
                    // Sisa 20% untuk random kategori
                    $categoryId = $categories->random()->id;
                }

                $amount = rand(5, 50) * 10000; // Antara 50,000 sampai 500,000

                Expense::create([
                    'expense_category_id' => $categoryId,
                    'user_id' => $user->id ?? 1,
                    'amount' => $amount,
                    'description' => 'Dummy Expense ' . $i . ' untuk keperluan operasional',
                    'date' => $date,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }
        }

        $this->command->info('Expense Test Seeder berhasil dijalankan. Data Dummy Pengeluaran telah dibuat.');
    }
}
