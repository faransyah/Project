<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['IN', 'OUT']);
            $table->dateTime('date')->useCurrent();
            
            // Relasi Item
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->onDelete('cascade') 
                  ->onUpdate('cascade');

            // --- TAMBAHAN BARIS INI (YANG TADINYA HILANG) ---
            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            // -----------------------------------------------

            $table->string('item_name_snapshot', 255);
            $table->integer('qty');
            $table->string('actor', 100)->default('System');
            $table->text('note')->nullable();
            
            $table->timestamps(); 
        });
    }
};