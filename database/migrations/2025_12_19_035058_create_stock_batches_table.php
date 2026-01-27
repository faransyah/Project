<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            
            // Relasi ke Stocks
            $table->foreignId('stock_id')
                  ->constrained('stocks')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->date('date');
            $table->decimal('price', 15, 2);
            $table->integer('stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_batches');
    }
};