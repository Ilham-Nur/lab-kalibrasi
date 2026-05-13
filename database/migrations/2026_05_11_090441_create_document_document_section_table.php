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
        Schema::create('document_document_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_section_id')->constrained('document_sections')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['document_id', 'document_section_id'], 'document_section_unique');
        });

        DB::table('documents')
            ->whereNotNull('section_id')
            ->orderBy('id')
            ->select(['id', 'section_id'])
            ->get()
            ->each(function (object $document): void {
                DB::table('document_document_section')->insertOrIgnore([
                    'document_id' => $document->id,
                    'document_section_id' => $document->section_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_document_section');
    }
};
