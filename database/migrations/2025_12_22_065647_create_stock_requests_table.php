<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_requests', function (Blueprint $table) {
            $table->id();
            // Siapa yang minta? (Terhubung ke tabel users)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Barang apa yang diminta? (Terhubung ke tabel items)
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            
            // Berapa banyak?
            $table->integer('quantity');
            
            // Statusnya apa? (Pending = Menunggu persetujuan admin)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Catatan tambahan (misal: "Buat acara rapat")
            $table->text('note')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_requests');
    }
};