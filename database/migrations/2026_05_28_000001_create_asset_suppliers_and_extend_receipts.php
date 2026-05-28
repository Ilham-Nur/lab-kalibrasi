<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->longText('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('asset_receipts', function (Blueprint $table) {
            $table->foreignId('asset_supplier_id')->nullable()->after('received_date')->constrained('asset_suppliers')->nullOnDelete();
        });

        Schema::table('asset_receipt_items', function (Blueprint $table) {
            $table->foreignId('asset_category_id')->nullable()->after('condition')->constrained('asset_categories')->nullOnDelete();
            $table->foreignId('asset_location_id')->nullable()->after('asset_category_id')->constrained('asset_locations')->nullOnDelete();
            $table->string('brand')->nullable()->after('asset_location_id');
            $table->string('model')->nullable()->after('brand');
            $table->string('serial_number')->nullable()->after('model');
            $table->longText('specification')->nullable()->after('serial_number');
            $table->decimal('acquisition_value', 15, 2)->nullable()->after('specification');
        });

        Schema::table('asset_documents', function (Blueprint $table) {
            $table->foreignId('receipt_id')->nullable()->after('procurement_id')->constrained('asset_receipts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('receipt_id');
        });

        Schema::table('asset_receipt_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_location_id');
            $table->dropConstrainedForeignId('asset_category_id');
            $table->dropColumn(['brand', 'model', 'serial_number', 'specification', 'acquisition_value']);
        });

        Schema::table('asset_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_supplier_id');
        });

        Schema::dropIfExists('asset_suppliers');
    }
};
