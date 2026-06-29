<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Supplier;
use App\Models\RawMaterial;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
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
                $category = Category::updateOrCreate(
                    ['slug' => Str::slug($catData['name'])],
                    [
                        'name' => $catData['name'],
                        'is_active' => true,
                    ]
                );

                if (isset($catData['menus'])) {
                    foreach ($catData['menus'] as $menuIndex => $menuName) {
                        Menu::updateOrCreate(
                            ['slug' => Str::slug($menuName)],
                            [
                                'category_id' => $category->id,
                                'name' => $menuName,
                                'selling_price' => 0.00,
                                'hpp' => 0.00,
                                'is_available' => true,
                                'is_active' => true,
                                'sort_order' => ($menuIndex + 1) * 10,
                            ]
                        );
                    }
                }
            }
        }

        // 2. Seed Raw Materials
        if (isset($data['raw_materials'])) {
            foreach ($data['raw_materials'] as $rmData) {
                RawMaterial::updateOrCreate(
                    ['name' => $rmData['name']],
                    [
                        'sku' => 'RM-' . strtoupper(Str::slug($rmData['name'])),
                        'unit' => $rmData['unit'],
                        'stock' => 0.000,
                        'minimum_stock' => 0.000,
                        'buy_price' => $rmData['buy_price'],
                        'is_active' => true,
                    ]
                );
            }
        }

        // 3. Seed Expense Categories
        if (isset($data['expense_categories'])) {
            foreach ($data['expense_categories'] as $index => $expName) {
                ExpenseCategory::updateOrCreate(
                    ['slug' => Str::slug($expName)],
                    [
                        'name' => $expName,
                        'is_active' => true,
                        'sort_order' => ($index + 1) * 10,
                    ]
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
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                ]
            );
        }
    }
}
