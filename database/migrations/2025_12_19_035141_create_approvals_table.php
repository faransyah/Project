<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('user_name', 100);
            
            // Relasi Unit & Item
            $table->foreignId('unit_id')
                  ->constrained('units')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreignId('item_id')
                  ->constrained('items')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->string('item_name_snapshot', 255);
            $table->integer('item_count');
            $table->string('value_estimate', 50)->nullable();
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            
            // GANTI DARI timestamp('created_at') JADI INI:
            $table->timestamps(); // Ini otomatis bikin 'created_at' DAN 'updated_at'
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};