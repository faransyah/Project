<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            
            // Relasi Item (Barang)
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            // Relasi Unit (Kantor) - Tabel units sudah ada kan? Jadi aman.
            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->integer('stock')->default(0);
            $table->integer('stock_min')->default(10);
            $table->decimal('price', 15, 2)->default(0.00);
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            
            $table->timestamps();

            // Mencegah duplikasi: 1 Barang cuma boleh ada 1x di 1 Unit
            $table->unique(['item_id', 'unit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};