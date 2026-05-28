<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_procurements', function (Blueprint $table) {
            $table->id();
            $table->string('procurement_number')->unique();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('request_date');
            $table->string('department')->nullable();
            $table->longText('purpose')->nullable();
            $table->decimal('total_estimated_cost', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->unsignedTinyInteger('current_approval_level')->nullable();
            $table->string('supervisor_status')->default('pending');
            $table->string('finance_status')->default('pending');
            $table->string('director_status')->default('pending');
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_procurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_id')->constrained('asset_procurements')->cascadeOnDelete();
            $table->string('item_name');
            $table->longText('specification')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('unit')->nullable();
            $table->decimal('estimated_unit_price', 15, 2)->default(0);
            $table->decimal('estimated_total_price', 15, 2)->default(0);
            $table->string('supplier_candidate')->nullable();
            $table->longText('reason')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_procurement_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_id')->constrained('asset_procurements')->cascadeOnDelete();
            $table->unsignedTinyInteger('approval_level');
            $table->string('role_name');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->longText('notes')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procurement_id')->constrained('asset_procurements')->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('received_date');
            $table->string('supplier_name')->nullable();
            $table->string('delivery_note_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('status')->default('draft');
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('asset_receipts')->cascadeOnDelete();
            $table->foreignId('procurement_item_id')->nullable()->constrained('asset_procurement_items')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity_ordered', 12, 2);
            $table->decimal('quantity_received', 12, 2);
            $table->string('condition')->nullable();
            $table->longText('notes')->nullable();
            $table->boolean('is_converted_to_asset')->default(false);
            $table->timestamps();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->foreignId('asset_category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->foreignId('asset_location_id')->nullable()->constrained('asset_locations')->nullOnDelete();
            $table->foreignId('procurement_id')->nullable()->constrained('asset_procurements')->nullOnDelete();
            $table->foreignId('receipt_id')->nullable()->constrained('asset_receipts')->nullOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            $table->longText('specification')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->decimal('acquisition_value', 15, 2)->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('source_type');
            $table->string('condition');
            $table->string('status');
            $table->boolean('is_measuring_equipment')->default(false);
            $table->boolean('requires_calibration')->default(false);
            $table->unsignedInteger('calibration_interval_months')->nullable();
            $table->date('last_calibration_date')->nullable();
            $table->date('next_calibration_date')->nullable();
            $table->boolean('requires_periodic_inspection')->default(false);
            $table->unsignedInteger('inspection_interval_months')->nullable();
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->string('old_condition')->nullable();
            $table->string('new_condition')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('asset_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->date('inspection_date');
            $table->date('next_inspection_date')->nullable();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('result');
            $table->string('status');
            $table->longText('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('asset_inspections')->cascadeOnDelete();
            $table->string('checklist_name');
            $table->string('result');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_calibrations', function (Blueprint $table) {
            $table->id();
            $table->string('calibration_number')->unique();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->date('calibration_date');
            $table->date('next_calibration_date')->nullable();
            $table->string('calibration_type');
            $table->string('calibration_provider')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('result');
            $table->string('status');
            $table->string('file_certificate')->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->longText('evaluation_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_calibration_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('calibration_id')->constrained('asset_calibrations')->cascadeOnDelete();
            $table->string('parameter')->nullable();
            $table->string('nominal_value')->nullable();
            $table->string('measured_value')->nullable();
            $table->string('correction')->nullable();
            $table->string('uncertainty')->nullable();
            $table->string('tolerance')->nullable();
            $table->string('result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->cascadeOnDelete();
            $table->foreignId('procurement_id')->nullable()->constrained('asset_procurements')->cascadeOnDelete();
            $table->foreignId('calibration_id')->nullable()->constrained('asset_calibrations')->cascadeOnDelete();
            $table->foreignId('inspection_id')->nullable()->constrained('asset_inspections')->cascadeOnDelete();
            $table->string('document_type')->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_documents');
        Schema::dropIfExists('asset_calibration_results');
        Schema::dropIfExists('asset_calibrations');
        Schema::dropIfExists('asset_inspection_items');
        Schema::dropIfExists('asset_inspections');
        Schema::dropIfExists('asset_status_logs');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('asset_receipt_items');
        Schema::dropIfExists('asset_receipts');
        Schema::dropIfExists('asset_procurement_approvals');
        Schema::dropIfExists('asset_procurement_items');
        Schema::dropIfExists('asset_procurements');
        Schema::dropIfExists('asset_locations');
        Schema::dropIfExists('asset_categories');
    }
};
