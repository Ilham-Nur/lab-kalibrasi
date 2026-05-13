<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeDocumentController extends Controller
{
    public function store(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'document_type' => ['required', 'in:KTP,KK,NPWP,BPJS,Ijazah Terakhir,Pengalaman Kerja,CV,Lainnya'],
            'document_name' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'expired_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $file = $request->file('file');
        $validated['employee_id'] = $employee->id;
        $validated['file_path'] = $file->store('hr/documents', 'public');
        $validated['file_original_name'] = $file->getClientOriginalName();
        $validated['uploaded_by'] = $request->user()?->id;
        unset($validated['file']);

        EmployeeDocument::create($validated);

        return back()->with('success', 'Dokumen karyawan berhasil diupload.');
    }

    public function download(EmployeeDocument $document)
    {
        abort_unless($document->file_path, 404);

        return Storage::disk('public')->download($document->file_path, $document->file_original_name);
    }

    public function destroy(EmployeeDocument $document)
    {
        Storage::disk('public')->delete($document->file_path);
        $document->delete();

        return back()->with('success', 'Dokumen karyawan berhasil dihapus.');
    }
}
