<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            // Tambah kolom 'code' jika belum ada (untuk Kode Batch Request)
            if (!Schema::hasColumn('stock_requests', 'code')) {
                $table->string('code')->nullable()->after('id')->index();
            }

            // Tambah kolom 'unit_id' jika belum ada (Penyebab Error Kamu)
            if (!Schema::hasColumn('stock_requests', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('user_id')->constrained('units')->nullOnDelete();
            }
        });
    }

    public function down()
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            // Hapus kolom jika rollback
            if (Schema::hasColumn('stock_requests', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
            if (Schema::hasColumn('stock_requests', 'code')) {
                $table->dropColumn('code');
            }
        });
    }
};