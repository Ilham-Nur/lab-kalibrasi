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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('original_file_path')->nullable()->after('file_path');
            $table->string('preview_file_path')->nullable()->after('original_file_path');
            $table->string('original_file_type')->nullable()->after('preview_file_path');
        });

        DB::table('documents')
            ->whereNotNull('file_path')
            ->update(['preview_file_path' => DB::raw('file_path')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'original_file_path',
                'preview_file_path',
                'original_file_type',
            ]);
        });
    }
};
