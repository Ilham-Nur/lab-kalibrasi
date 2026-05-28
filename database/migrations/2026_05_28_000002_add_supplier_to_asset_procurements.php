<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_procurements', function (Blueprint $table) {
            $table->foreignId('asset_supplier_id')->nullable()->after('requested_by')->constrained('asset_suppliers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_procurements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_supplier_id');
        });
    }
};
