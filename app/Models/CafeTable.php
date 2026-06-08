<?php

namespace App\Models;

use App\Enums\TableStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CafeTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cafe_tables';

    protected $fillable = [
        'table_number',
        'name',
        'capacity',
        'qr_code',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'status' => TableStatus::class,
            'is_active' => 'boolean',
        ];
    }
}
