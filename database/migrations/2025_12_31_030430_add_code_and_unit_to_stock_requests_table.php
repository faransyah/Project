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
    Schema::table('stock_requests', function (Blueprint $table) {
        $table->string('code')->nullable()->after('id')->index(); // Kode unik per batch request (Misal: REQ-20250101-001)
        $table->foreignId('unit_id')->nullable()->after('user_id')->constrained('units')->nullOnDelete();
    });
}

    public function down()
    {
        Schema::table('stock_requests', function (Blueprint $table) {
            $table->dropColumn(['code', 'unit_id']);
        });
    }
};
