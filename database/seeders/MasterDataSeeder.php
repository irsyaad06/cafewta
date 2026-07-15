<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Supplier;
use App\Models\RawMaterial;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Recipe;
use App\Enums\PaymentMethodType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Path to JSON file
        $jsonPath = database_path('seeders/data_from_xlsx.json');
        
        if (!file_exists($jsonPath)) {
            return;
        }
        
        $data = json_decode(file_get_contents($jsonPath), true);

        // 1. Seed Categories & Menus
        if (isset($data['categories'])) {
            foreach ($data['categories'] as $index => $catData) {
                $category = Category::firstOrCreate(
                    ['name' => $catData['name']]
                );

                if (isset($catData['menus'])) {
                    foreach ($catData['menus'] as $menuIndex => $menuName) {
                        $dummyPrice = rand(10, 50) * 1000;
                        $dummyHpp = $dummyPrice * 0.5;

                        Menu::firstOrCreate(
                            ['name' => $menuName, 'category_id' => $category->id],
                            [
                                'selling_price' => $dummyPrice,
                                'hpp' => $dummyHpp,
                                'is_available' => true,
                            ]
                        );
                    }
                }
            }
        }

        // 2. Seed Raw Materials
        if (isset($data['raw_materials'])) {
            foreach ($data['raw_materials'] as $rmData) {
                RawMaterial::firstOrCreate(
                    ['name' => $rmData['name']],
                    [
                        'unit' => $rmData['unit'],
                        'stock' => 1000.000,
                        'minimum_stock' => 10.000,
                        'buy_price' => $rmData['buy_price'],
                    ]
                );
            }
        }

        // 3. Seed Recipes (Randomly attach 1-3 raw materials per menu)
        $allMenus = Menu::all();
        $allRawMaterials = RawMaterial::all();
        
        if ($allRawMaterials->isNotEmpty()) {
            foreach ($allMenus as $menu) {
                // Determine how many ingredients for this menu
                $numIngredients = rand(1, 3);
                $selectedRawMaterials = $allRawMaterials->random(min($numIngredients, $allRawMaterials->count()));

                foreach ($selectedRawMaterials as $rm) {
                    Recipe::firstOrCreate(
                        ['menu_id' => $menu->id, 'raw_material_id' => $rm->id],
                        [
                            'quantity' => rand(1, 5),
                            'unit' => $rm->unit,
                        ]
                    );
                }
            }
        }

        // 3. Seed Expense Categories
        if (isset($data['expense_categories'])) {
            foreach ($data['expense_categories'] as $index => $expName) {
                ExpenseCategory::firstOrCreate(
                    ['name' => $expName]
                );
            }
        }

        // 4. Seed Payment Methods
        $paymentMethods = [
            ['name' => 'Cash', 'code' => 'cash', 'type' => PaymentMethodType::Cash],
            ['name' => 'QRIS', 'code' => 'qris', 'type' => PaymentMethodType::Qris],
            ['name' => 'Transfer', 'code' => 'transfer', 'type' => PaymentMethodType::Transfer],
            ['name' => 'Debit', 'code' => 'debit', 'type' => PaymentMethodType::Debit],
        ];
        foreach ($paymentMethods as $index => $pm) {
            PaymentMethod::updateOrCreate(
                ['code' => $pm['code']],
                [
                    'name' => $pm['name'],
                    'type' => $pm['type'],
                ]
            );
        }

        // 5. Seed Cafe Tables
        for ($i = 1; $i <= 15; $i++) {
            \App\Models\CafeTable::firstOrCreate(
                ['table_number' => strval($i)],
                [
                    'name' => 'Meja ' . $i,
                    'capacity' => 4,
                    'status' => \App\Enums\TableStatus::Available,
                    'qr_code' => url('/order/' . $i),
                ]
            );
        }
    }
}
