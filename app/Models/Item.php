<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;

    // Pastikan ini guarded kosong (artinya semua kolom boleh diisi)
    // ATAU tambahkan 'url_photo' ke dalam $fillable jika kamu pakai fillable
    protected $guarded = []; 

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}