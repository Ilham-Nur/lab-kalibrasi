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
        $employee->load([
            'division',
            'position',
            'documents' => fn ($query) => $query->latest(),
            'certificates' => fn ($query) => $query->latest(),
            'attendances' => fn ($query) => $query->latest('attendance_date')->limit(10),
            'salaries' => fn ($query) => $query->latest()->limit(10),
        ]);

        $jobDescriptions = JobDescription::query()
            ->with(['division', 'position', 'directSupervisor'])
            ->where('status', 'aktif')
            ->when($employee->division_id, fn ($query) => $query->where('division_id', $employee->division_id))
            ->when($employee->position_id, fn ($query) => $query->where('position_id', $employee->position_id))
            ->get();

        return view('hr.employees.show', [
            'employee' => $employee,
            'jobDescriptions' => $jobDescriptions,
            'documentTypes' => $this->documentTypes(),
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
            'jumlah_anak' => ['nullable', 'integer', 'min:0'],
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
        return ['KTP', 'KK', 'NPWP', 'BPJS', 'Ijazah Terakhir', 'Pengalaman Kerja', 'CV', 'Lainnya'];
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
