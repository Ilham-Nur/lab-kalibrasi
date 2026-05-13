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
        Schema::table('document_revisions', function (Blueprint $table) {
            $table->string('document_code')->nullable()->after('document_id');
            $table->string('title')->nullable()->after('document_code');
            $table->text('description')->nullable()->after('title');
            $table->string('status')->nullable()->after('description');
            $table->json('section_ids')->nullable()->after('status');
        });

        DB::table('document_revisions')
            ->join('documents', 'document_revisions.document_id', '=', 'documents.id')
            ->select([
                'document_revisions.id',
                'documents.document_code',
                'documents.title',
                'documents.description',
                'documents.status',
                'documents.section_id',
            ])
            ->orderBy('document_revisions.id')
            ->get()
            ->each(function (object $revision): void {
                DB::table('document_revisions')
                    ->where('id', $revision->id)
                    ->update([
                        'document_code' => $revision->document_code,
                        'title' => $revision->title,
                        'description' => $revision->description,
                        'status' => $revision->status,
                        'section_ids' => $revision->section_id ? json_encode([(int) $revision->section_id]) : null,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_revisions', function (Blueprint $table) {
            $table->dropColumn([
                'document_code',
                'title',
                'description',
                'status',
                'section_ids',
            ]);
        });
    }
};
