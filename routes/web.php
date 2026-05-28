<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AssetCalibrationController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetConvertController;
use App\Http\Controllers\AssetDashboardController;
use App\Http\Controllers\AssetInspectionController;
use App\Http\Controllers\AssetProcurementApprovalController;
use App\Http\Controllers\AssetProcurementController;
use App\Http\Controllers\AssetReceiptController;
use App\Http\Controllers\AssetReportController;
use App\Http\Controllers\AssetSupplierController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\DivisionController;
use App\Http\Controllers\Hr\EmployeeCertificateController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\EmployeeDocumentController;
use App\Http\Controllers\Hr\JobDescriptionController;
use App\Http\Controllers\Hr\PositionController;
use App\Http\Controllers\Hr\RecruitmentCandidateController;
use App\Http\Controllers\Hr\RecruitmentRequestController;
use App\Http\Controllers\Hr\SalaryController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::get('/login', [LoginController::class, 'showLoginForm']);
    Route::post('/login', [LoginController::class, 'login'])->name('login.process');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('permission:dashboard.view')->name('dashboard');

    Route::prefix('management')->name('management.')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:users.view')
            ->middlewareFor(['create', 'store'], 'permission:users.create')
            ->middlewareFor(['edit', 'update'], 'permission:users.edit')
            ->middlewareFor(['destroy'], 'permission:users.delete');
        Route::resource('roles', RoleManagementController::class)->except(['show'])
            ->middlewareFor(['index'], 'permission:roles.view')
            ->middlewareFor(['create', 'store'], 'permission:roles.create')
            ->middlewareFor(['edit', 'update'], 'permission:roles.edit')
            ->middlewareFor(['destroy'], 'permission:roles.delete');
    });

    Route::resource('suppliers', AssetSupplierController::class)->except(['show'])
        ->middlewareFor(['index'], 'permission:asset-suppliers.view')
        ->middlewareFor(['create', 'store'], 'permission:asset-suppliers.create')
        ->middlewareFor(['edit', 'update'], 'permission:asset-suppliers.edit')
        ->middlewareFor(['destroy'], 'permission:asset-suppliers.delete');

    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('dashboard', [AssetDashboardController::class, 'index'])->middleware('permission:assets.view')->name('dashboard');
        Route::get('import', [AssetController::class, 'importForm'])->middleware('permission:assets.create')->name('import');
        Route::post('import', [AssetController::class, 'importStore'])->middleware('permission:assets.create')->name('import.store');

        Route::resource('inspections', AssetInspectionController::class)
            ->middlewareFor(['index', 'show'], 'permission:asset-inspections.view')
            ->middlewareFor(['create', 'store'], 'permission:asset-inspections.create')
            ->middlewareFor(['edit', 'update'], 'permission:asset-inspections.edit')
            ->middlewareFor(['destroy'], 'permission:asset-inspections.delete');
        Route::resource('calibrations', AssetCalibrationController::class)
            ->middlewareFor(['index', 'show'], 'permission:asset-calibrations.view')
            ->middlewareFor(['create', 'store'], 'permission:asset-calibrations.create')
            ->middlewareFor(['edit', 'update'], 'permission:asset-calibrations.edit')
            ->middlewareFor(['destroy'], 'permission:asset-calibrations.delete');

        Route::get('procurements/approvals', [AssetProcurementApprovalController::class, 'index'])->middleware('permission:asset-procurements.approve')->name('procurements.approvals.index');
        Route::get('procurements/approvals/{procurement}', [AssetProcurementApprovalController::class, 'show'])->middleware('permission:asset-procurements.approve')->name('procurements.approvals.show');
        Route::post('procurements/approvals/{procurement}/approve', [AssetProcurementApprovalController::class, 'approve'])->middleware('permission:asset-procurements.approve')->name('procurements.approvals.approve');
        Route::post('procurements/approvals/{procurement}/reject', [AssetProcurementApprovalController::class, 'reject'])->middleware('permission:asset-procurements.approve')->name('procurements.approvals.reject');
        Route::post('procurements/approvals/{procurement}/revision', [AssetProcurementApprovalController::class, 'revision'])->middleware('permission:asset-procurements.approve')->name('procurements.approvals.revision');
        Route::post('procurements/{procurement}/submit', [AssetProcurementController::class, 'submit'])->middleware('permission:asset-procurements.create')->name('procurements.submit');
        Route::resource('procurements', AssetProcurementController::class)
            ->middlewareFor(['index', 'show'], 'permission:asset-procurements.view')
            ->middlewareFor(['create', 'store'], 'permission:asset-procurements.create')
            ->middlewareFor(['edit', 'update'], 'permission:asset-procurements.edit')
            ->middlewareFor(['destroy'], 'permission:asset-procurements.delete');

        Route::resource('receipts', AssetReceiptController::class)
            ->middlewareFor(['index', 'show'], 'permission:asset-receipts.view')
            ->middlewareFor(['create', 'store'], 'permission:asset-receipts.create')
            ->middlewareFor(['edit', 'update'], 'permission:asset-receipts.edit')
            ->middlewareFor(['destroy'], 'permission:asset-receipts.delete');
        Route::get('convert', [AssetConvertController::class, 'index'])->middleware('permission:asset-conversions.view')->name('convert.index');
        Route::get('convert/{receipt}', [AssetConvertController::class, 'show'])->middleware('permission:asset-conversions.view')->name('convert.show');
        Route::post('convert/item/{receiptItem}', [AssetConvertController::class, 'convert'])->middleware('permission:asset-conversions.create')->name('convert.item');

        Route::get('reports', [AssetReportController::class, 'index'])->middleware('permission:asset-reports.view')->name('reports.index');
        Route::get('reports/export-pdf', [AssetReportController::class, 'exportPdf'])->middleware('permission:asset-reports.view')->name('reports.export-pdf');
        Route::get('reports/export-excel', [AssetReportController::class, 'exportExcel'])->middleware('permission:asset-reports.view')->name('reports.export-excel');

        Route::get('/', [AssetController::class, 'index'])->middleware('permission:assets.view')->name('index');
        Route::get('create', [AssetController::class, 'create'])->middleware('permission:assets.create')->name('create');
        Route::post('/', [AssetController::class, 'store'])->middleware('permission:assets.create')->name('store');
        Route::get('{asset}/history', [AssetController::class, 'history'])->middleware('permission:assets.view')->name('history');
        Route::get('{asset}', [AssetController::class, 'show'])->middleware('permission:assets.view')->name('show');
        Route::get('{asset}/edit', [AssetController::class, 'edit'])->middleware('permission:assets.edit')->name('edit');
        Route::put('{asset}', [AssetController::class, 'update'])->middleware('permission:assets.edit')->name('update');
        Route::delete('{asset}', [AssetController::class, 'destroy'])->middleware('permission:assets.delete')->name('destroy');
    });

    Route::prefix('hr')->name('hr.')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::resource('divisions', DivisionController::class)->except(['show']);
        Route::resource('positions', PositionController::class)->except(['show']);
        Route::post('employees/{employee}/documents', [EmployeeDocumentController::class, 'store'])->name('employees.documents.store');
        Route::get('employee-documents/{document}/download', [EmployeeDocumentController::class, 'download'])->name('employee-documents.download');
        Route::delete('employee-documents/{document}', [EmployeeDocumentController::class, 'destroy'])->name('employee-documents.destroy');
        Route::post('employees/{employee}/certificates', [EmployeeCertificateController::class, 'store'])->name('employees.certificates.store');
        Route::put('employee-certificates/{certificate}', [EmployeeCertificateController::class, 'update'])->name('employee-certificates.update');
        Route::get('employee-certificates/{certificate}/download', [EmployeeCertificateController::class, 'download'])->name('employee-certificates.download');
        Route::delete('employee-certificates/{certificate}', [EmployeeCertificateController::class, 'destroy'])->name('employee-certificates.destroy');
        Route::resource('recruitments', RecruitmentRequestController::class);
        Route::patch('recruitments/{recruitment}/status', [RecruitmentRequestController::class, 'updateStatus'])->name('recruitments.status');
        Route::post('recruitments/{recruitment}/candidates', [RecruitmentCandidateController::class, 'store'])->name('recruitments.candidates.store');
        Route::put('recruitment-candidates/{candidate}', [RecruitmentCandidateController::class, 'update'])->name('recruitment-candidates.update');
        Route::delete('recruitment-candidates/{candidate}', [RecruitmentCandidateController::class, 'destroy'])->name('recruitment-candidates.destroy');
        Route::resource('job-descriptions', JobDescriptionController::class);
        Route::resource('attendances', AttendanceController::class)->except(['show']);
        Route::resource('salaries', SalaryController::class);
        Route::patch('salaries/{salary}/mark-paid', [SalaryController::class, 'markPaid'])->name('salaries.mark-paid');
        Route::get('salaries/{salary}/slip', [SalaryController::class, 'slip'])->name('salaries.slip');
    });
    Route::post('/dokumen-iso/standards', [DocumentController::class, 'storeStandard'])->name('dokumen-iso.standards.store');
    Route::get('/dokumen-iso/17025', [DocumentController::class, 'iso17025'])->name('dokumen-iso.17025.index');
    Route::get('/dokumen-iso/{standard}', [DocumentController::class, 'standard'])->name('dokumen-iso.standard.index');
    Route::post('/dokumen-iso/{standard}/documents', [DocumentController::class, 'store'])->name('dokumen-iso.documents.store');
    Route::get('/dokumen-iso/{standard}/documents/{document}', [DocumentController::class, 'show'])->name('dokumen-iso.documents.show');
    Route::get('/dokumen-iso/{standard}/documents/{document}/preview', [DocumentController::class, 'preview'])->name('dokumen-iso.documents.preview');
    Route::get('/dokumen-iso/{standard}/documents/{document}/original', [DocumentController::class, 'downloadDocumentOriginal'])->name('dokumen-iso.documents.original');
    Route::get('/dokumen-iso/{standard}/documents/{document}/pdf', [DocumentController::class, 'downloadDocumentPdf'])->name('dokumen-iso.documents.pdf');
    Route::post('/dokumen-iso/{standard}/sections', [DocumentController::class, 'storeSection'])->name('dokumen-iso.sections.store');
    Route::put('/dokumen-iso/{standard}/sections/{section}', [DocumentController::class, 'updateSection'])->name('dokumen-iso.sections.update');
    Route::delete('/dokumen-iso/{standard}/sections/{section}', [DocumentController::class, 'destroySection'])->name('dokumen-iso.sections.destroy');
    Route::get('/dokumen-iso/17025/revisions/{revision}/original', [DocumentController::class, 'downloadOriginal'])->name('dokumen-iso.17025.revisions.original');
    Route::get('/dokumen-iso/17025/revisions/{revision}/pdf', [DocumentController::class, 'downloadPdf'])->name('dokumen-iso.17025.revisions.pdf');
});
