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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('spouse_nik_ktp')->nullable()->after('status_pernikahan');
            $table->json('children_nik')->nullable()->after('jumlah_anak');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['spouse_nik_ktp', 'children_nik']);
        });
    }
};
