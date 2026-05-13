<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('document_standards')->insertOrIgnore([
            [
                'id' => 1,
                'name' => 'ISO 9001',
                'slug' => '9001',
                'order_number' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'ISO 17025',
                'slug' => '17025',
                'order_number' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('standard_id')->nullable()->after('id')->constrained('document_standards')->cascadeOnDelete();
        });

        Schema::table('document_sections', function (Blueprint $table) {
            $table->foreignId('standard_id')->nullable()->after('id')->constrained('document_standards')->cascadeOnDelete();
        });

        DB::table('documents')->whereNull('standard_id')->update(['standard_id' => 2]);
        DB::table('document_sections')->whereNull('standard_id')->update(['standard_id' => 2]);

        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique('documents_document_code_unique');
            $table->unique(['standard_id', 'category_id', 'document_code'], 'documents_standard_category_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique('documents_standard_category_code_unique');
            $table->unique('document_code', 'documents_document_code_unique');
            $table->dropConstrainedForeignId('standard_id');
        });

        Schema::table('document_sections', function (Blueprint $table) {
            $table->dropConstrainedForeignId('standard_id');
        });
    }
};
