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
    Schema::table('stocks', function (Blueprint $table) {
        // Buat nullable() dulu biar data stok lama gak error
        $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('unit_id');
    });
}

    public function down()
    {
        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
