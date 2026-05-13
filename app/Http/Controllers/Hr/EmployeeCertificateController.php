<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeCertificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeCertificateController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $validated = $this->validatedCertificate($request);
        $validated['employee_id'] = $employee->id;

        if ($request->hasFile('file')) {
            $validated['file_path'] = $request->file('file')->store('hr/certificates', 'public');
        }

        EmployeeCertificate::create($validated);

        return back()->with('success', 'Sertifikat karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, EmployeeCertificate $certificate)
    {
        $validated = $this->validatedCertificate($request);

        if ($request->hasFile('file')) {
            Storage::disk('public')->delete($certificate->file_path);
            $validated['file_path'] = $request->file('file')->store('hr/certificates', 'public');
        }

        $certificate->update($validated);

        return back()->with('success', 'Sertifikat karyawan berhasil diperbarui.');
    }

    public function download(EmployeeCertificate $certificate)
    {
        abort_unless($certificate->file_path, 404);

        return Storage::disk('public')->download($certificate->file_path);
    }

    public function destroy(EmployeeCertificate $certificate)
    {
        Storage::disk('public')->delete($certificate->file_path);
        $certificate->delete();

        return back()->with('success', 'Sertifikat karyawan berhasil dihapus.');
    }

    private function validatedCertificate(Request $request): array
    {
        return $request->validate([
            'certificate_title' => ['required', 'string', 'max:255'],
            'certificate_number' => ['nullable', 'string', 'max:255'],
            'issuer' => ['nullable', 'string', 'max:255'],
            'execution_date' => ['nullable', 'date'],
            'expired_date' => ['nullable', 'date'],
            'certificate_type' => ['required', 'in:internal,external'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
