<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id(); // Ini yang dicari sama users (unit_id)
            $table->string('alias'); // UID Jatim, UID Jabar
            $table->string('name');  // Nama Lengkap PT PLN...
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('ref_id')->nullable(); // Kode unik (UID-001)
            $table->integer('parent_id')->default(0); // 0 = Kantor Induk
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};