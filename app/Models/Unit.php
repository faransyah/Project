<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    // Relasi untuk cek sebelum hapus
    public function users() { return $this->hasMany(User::class); }
    public function stocks() { return $this->hasMany(Stock::class); }
}