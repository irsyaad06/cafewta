<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'name',
        'unit',
        'stock',
        'minimum_stock',
        'buy_price',
    ];

    protected $appends = ['stock_status'];

    protected function casts(): array
    {
        return [
            'stock' => 'decimal:3',
            'minimum_stock' => 'decimal:3',
            'buy_price' => 'decimal:2',
        ];
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->stock <= 0) return 'habis';
        if ($this->stock <= $this->minimum_stock) return 'tipis';
        return 'aman';
    }

    protected static function booted()
    {
        static::saved(function ($rawMaterial) {
            if ($rawMaterial->stock <= 0) {
                $menuIds = Recipe::where('raw_material_id', $rawMaterial->id)->pluck('menu_id');
                Menu::whereIn('id', $menuIds)->update(['is_available' => false]);
            }
        });
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }
}
