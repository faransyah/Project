<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('stock_histories', function (Blueprint $table) {
        // Tambah kolom price jika belum ada
        if (!Schema::hasColumn('stock_histories', 'price')) {
            $table->decimal('price', 15, 2)->default(0)->after('qty');
        }
    });
}

    public function down()
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
