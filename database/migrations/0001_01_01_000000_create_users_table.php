<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // --- IDENTITAS UTAMA ---
            $table->string('full_name'); // Di Vue: full_name
            $table->string('username')->unique()->nullable(); // Di Vue: username
            $table->string('email')->unique(); // Login utama
            $table->string('nip')->nullable()->unique(); // Di Vue: nip
            
            // --- KONTAK & JABATAN ---
            $table->string('phone')->nullable(); // Di Vue: phone
            $table->string('position_name')->nullable(); // Di Vue: position_name (Jabatan)
            
            // --- RELASI UNIT KERJA (PENTING!) ---
            // Pastikan tabel 'units' sudah dibuat sebelum migration ini jalan.
            // nullable() -> jaga-jaga kalau ada Super Admin Pusat yg gak terikat unit.
            $table->foreignId('unit_id')
                  ->nullable()
                  ->constrained('units')
                  ->nullOnDelete(); 

            // --- OTENTIKASI & KEAMANAN ---
            $table->string('role')->default('User'); // 'Admin' atau 'User'
            $table->boolean('is_active')->default(true); // 1 = Aktif, 0 = Non-Aktif
            $table->string('password');
            $table->string('url_photo')->nullable(); // Path foto profil
            
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            
            // Audit Log Sederhana
            $table->string('created_by')->nullable(); // Siapa yg input (opsional)
            $table->timestamps(); // created_at & updated_at
        });

        // Tabel Reset Password (Bawaan Laravel - Biarkan Saja)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Tabel Sessions (Bawaan Laravel - Biarkan Saja)
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};