<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobDescription;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::query()
            ->with(['division', 'position'])
            ->when(request('search'), fn ($query, $search) => $query->where(function ($subQuery) use ($search) {
                $subQuery->where('nama', 'like', "%{$search}%")
                    ->orWhere('nik_ktp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            }))
            ->when(request('division_id'), fn ($query, $divisionId) => $query->where('division_id', $divisionId))
            ->when(request('position_id'), fn ($query, $positionId) => $query->where('position_id', $positionId))
            ->when(request('status_karyawan'), fn ($query, $status) => $query->where('status_karyawan', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.employees.index', [
            'employees' => $employees,
            'divisions' => Division::orderBy('name')->get(),
            'positions' => Position::with('division')->orderBy('name')->get(),
            'statuses' => $this->employeeStatuses(),
        ]);
    }

    public function create()
    {
        return view('hr.employees.form', $this->formData(new Employee()));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedEmployee($request);
        $validated['children_nik'] = $this->childrenNikFromRequest($request);
        unset($validated['children_nik_text']);
        $validated['created_by'] = $request->user()?->id;
        $validated['updated_by'] = $request->user()?->id;

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('hr/employees/photos', 'public');
        }

        $employee = Employee::create($validated);

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['division', 'position']);
        $documents = $employee->documents()->latest()->paginate(5, ['*'], 'documents_page')->withQueryString();
        $internalCertificates = $employee->certificates()->where('certificate_type', 'internal')->latest()->paginate(5, ['*'], 'internal_certificates_page')->withQueryString();
        $externalCertificates = $employee->certificates()->where('certificate_type', 'external')->latest()->paginate(5, ['*'], 'external_certificates_page')->withQueryString();
        $attendances = $employee->attendances()->latest('attendance_date')->paginate(5, ['*'], 'attendances_page')->withQueryString();
        $salaries = $employee->salaries()->latest()->paginate(5, ['*'], 'salaries_page')->withQueryString();

        $jobDescriptions = JobDescription::query()
            ->with(['division', 'position', 'directSupervisor'])
            ->where('status', 'aktif')
            ->when($employee->division_id, fn ($query) => $query->where('division_id', $employee->division_id))
            ->when($employee->position_id, fn ($query) => $query->where('position_id', $employee->position_id))
            ->get();

        return view('hr.employees.show', [
            'employee' => $employee,
            'jobDescriptions' => $jobDescriptions,
            'documents' => $documents,
            'internalCertificates' => $internalCertificates,
            'externalCertificates' => $externalCertificates,
            'attendances' => $attendances,
            'salaries' => $salaries,
            'documentTypes' => $this->documentTypes(),
            'requiredDocuments' => $this->requiredDocuments($employee),
            'certificateTypes' => ['internal', 'external'],
        ]);
    }

    public function edit(Employee $employee)
    {
        return view('hr.employees.form', $this->formData($employee));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $this->validatedEmployee($request, $employee);
        $validated['children_nik'] = $this->childrenNikFromRequest($request);
        unset($validated['children_nik_text']);
        $validated['updated_by'] = $request->user()?->id;

        if ($request->hasFile('foto')) {
            $this->deleteFile($employee->foto);
            $validated['foto'] = $request->file('foto')->store('hr/employees/photos', 'public');
        }

        $employee->update($validated);

        return redirect()->route('hr.employees.show', $employee)->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $this->deleteFile($employee->foto);
        foreach ($employee->documents as $document) {
            $this->deleteFile($document->file_path);
        }
        foreach ($employee->certificates as $certificate) {
            $this->deleteFile($certificate->file_path);
        }
        foreach ($employee->attendances as $attendance) {
            $this->deleteFile($attendance->photo);
        }

        $employee->delete();

        return redirect()->route('hr.employees.index')->with('success', 'Data karyawan berhasil dihapus.');
    }

    private function formData(Employee $employee): array
    {
        return [
            'employee' => $employee,
            'divisions' => Division::where('status', 'aktif')->orderBy('name')->get(),
            'positions' => Position::with('division')->where('status', 'aktif')->orderBy('name')->get(),
            'statuses' => $this->employeeStatuses(),
            'genders' => ['laki-laki', 'perempuan'],
            'maritalStatuses' => ['belum_menikah', 'menikah', 'cerai'],
            'childrenNikText' => old('children_nik_text', collect($employee->children_nik ?? [])->implode("\n")),
        ];
    }

    private function validatedEmployee(Request $request, ?Employee $employee = null): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nik_ktp' => ['required', 'string', 'max:255', Rule::unique('employees', 'nik_ktp')->ignore($employee)],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'],
            'status_pernikahan' => ['nullable', 'in:belum_menikah,menikah,cerai'],
            'spouse_nik_ktp' => ['nullable', 'required_if:status_pernikahan,menikah', 'string', 'max:255'],
            'jumlah_anak' => ['nullable', 'integer', 'min:0'],
            'children_nik_text' => ['nullable', 'string'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'tanggal_masuk' => ['nullable', 'date'],
            'status_karyawan' => ['required', 'in:aktif,resign,kontrak,probation'],
            'no_npwp' => ['nullable', 'string', 'max:255'],
            'no_bpjs_kesehatan' => ['nullable', 'string', 'max:255'],
            'no_bpjs_ketenagakerjaan' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function employeeStatuses(): array
    {
        return ['aktif', 'resign', 'kontrak', 'probation'];
    }

    private function documentTypes(): array
    {
        return ['KTP', 'KTP Istri', 'KTP Anak', 'KK', 'NPWP', 'BPJS Kesehatan', 'BPJS Ketenagakerjaan', 'Ijazah Terakhir', 'Pengalaman Kerja', 'CV', 'Lainnya'];
    }

    private function requiredDocuments(Employee $employee): array
    {
        $requirements = [
            ['type' => 'KTP', 'label' => 'KTP Karyawan', 'required' => filled($employee->nik_ktp)],
            ['type' => 'NPWP', 'label' => 'NPWP', 'required' => filled($employee->no_npwp)],
            ['type' => 'BPJS Kesehatan', 'label' => 'BPJS Kesehatan', 'required' => filled($employee->no_bpjs_kesehatan)],
            ['type' => 'BPJS Ketenagakerjaan', 'label' => 'BPJS Ketenagakerjaan', 'required' => filled($employee->no_bpjs_ketenagakerjaan)],
            ['type' => 'KK', 'label' => 'Kartu Keluarga', 'required' => true],
            ['type' => 'Ijazah Terakhir', 'label' => 'Ijazah Terakhir', 'required' => true],
            ['type' => 'Pengalaman Kerja', 'label' => 'Surat Pengalaman Kerja', 'required' => true],
            ['type' => 'KTP Istri', 'label' => 'KTP Istri/Suami', 'required' => $employee->status_pernikahan === 'menikah'],
        ];

        return collect($requirements)
            ->filter(fn (array $item) => $item['required'])
            ->map(function (array $item) use ($employee) {
                $item['uploaded'] = $employee->documents()->where('document_type', $item['type'])->exists();

                return $item;
            })
            ->values()
            ->all();
    }

    private function childrenNikFromRequest(Request $request): array
    {
        $jumlahAnak = (int) $request->input('jumlah_anak', 0);
        $childrenNik = collect(preg_split('/\r\n|\r|\n/', (string) $request->input('children_nik_text')))
            ->map(fn (string $nik) => trim($nik))
            ->filter()
            ->values();

        if ($jumlahAnak > 0 && $childrenNik->count() < $jumlahAnak) {
            throw ValidationException::withMessages([
                'children_nik_text' => 'NIK anak wajib diisi minimal sesuai jumlah anak.',
            ]);
        }

        return $childrenNik->take($jumlahAnak)->all();
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
