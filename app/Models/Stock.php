<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    // IZINKAN KOLOM INI DIISI OTOMATIS
    protected $fillable = [
        'item_id',
        'unit_id',  // <--- Penting
        'user_id',  // <--- Penting
        'stock',
        'stock_min',
        'price',
        'status'
    ];

    public function item() { return $this->belongsTo(Item::class); }
    public function unit() { return $this->belongsTo(Unit::class); }
    public function user() { return $this->belongsTo(User::class); }
}   