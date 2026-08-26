<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'user_id',
        'date',
        'status',
        'total_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function processCompletion(): void
    {
        $this->refresh();
        $this->load('items.rawMaterial', 'supplier');
        
        // 1. Update Raw Material Stock and Supplier
        foreach ($this->items as $item) {
            $material = $item->rawMaterial;
            if ($material) {
                $material->stock += $item->quantity;
                $material->supplier_id = $this->supplier_id;
                $material->save();
            }
        }

        // 2. Create Expense
        $category = \App\Models\ExpenseCategory::firstOrCreate(
            ['name' => 'Pembelian Bahan Baku'],
            ['description' => 'Kategori otomatis untuk pembelian bahan baku']
        );

        \App\Models\Expense::create([
            'expense_category_id' => $category->id,
            'user_id' => $this->user_id ?? auth()->id(),
            'amount' => $this->total_price,
            'description' => 'Pembelian bahan baku ke ' . ($this->supplier->name ?? 'Pemasok') . ' (Pesanan #' . $this->id . ')',
            'date' => $this->date,
        ]);
    }
}
