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
    Schema::table('items', function (Blueprint $table) {
        // Tambah kolom created_by & updated_by jika belum ada
        if (!Schema::hasColumn('items', 'created_by')) {
            $table->string('created_by')->nullable()->after('status');
        }
        if (!Schema::hasColumn('items', 'updated_by')) {
            $table->string('updated_by')->nullable()->after('created_by');
        }
    });
}

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};
