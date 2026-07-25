<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CafeTable extends Model
{
    use HasFactory;

    protected $table = 'cafe_tables';

    protected $fillable = [
        'table_number',
        'name',
        'capacity',
        'qr_code',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'status' => TableStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CafeTable $table) {
            if (empty($table->qr_code)) {
                $table->qr_code = url('/order/' . $table->table_number);
            }
        });
    }
}
