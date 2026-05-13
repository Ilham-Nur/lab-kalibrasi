<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Hr\AttendanceController;
use App\Http\Controllers\Hr\EmployeeCertificateController;
use App\Http\Controllers\Hr\EmployeeController;
use App\Http\Controllers\Hr\EmployeeDocumentController;
use App\Http\Controllers\Hr\JobDescriptionController;
use App\Http\Controllers\Hr\RecruitmentCandidateController;
use App\Http\Controllers\Hr\RecruitmentRequestController;
use App\Http\Controllers\Hr\SalaryController;
use App\Http\Controllers\LoginController;
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::prefix('hr')->name('hr.')->group(function () {
        Route::resource('employees', EmployeeController::class);
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
