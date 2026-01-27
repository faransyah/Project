<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    use HasFactory;

    // KITA TAMBAHKAN 'code' DAN 'price' AGAR BISA DISIMPAN
    protected $fillable = [
        'code',      // <--- Penting untuk Grouping
        'user_id',
        'item_id',
        'unit_id',
        'quantity',
        'price',     // <--- Penting untuk Laporan Nilai Rupiah
        'status',
        'note',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
    
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}