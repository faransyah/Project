<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 255);
            
            // Relasi ke Categories
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');

            $table->text('description')->nullable();
            $table->integer('min_stock')->default(0);
            $table->integer('max_stock')->default(0);
            $table->decimal('price', 15, 2)->default(0.00);
            $table->string('uom', 20); // Satuan (Pcs, Rim, dll)
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->string('url_photo', 255)->nullable();
            $table->string('created_by', 100)->default('System');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};