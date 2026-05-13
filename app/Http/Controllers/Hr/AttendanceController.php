<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with('employee')
            ->when(request('employee_id'), fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->when(request('date_from'), fn ($query, $date) => $query->whereDate('attendance_date', '>=', $date))
            ->when(request('date_to'), fn ($query, $date) => $query->whereDate('attendance_date', '<=', $date))
            ->latest('attendance_date')
            ->paginate(10)
            ->withQueryString();

        $recapMonth = request('month', now()->month);
        $recapYear = request('year', now()->year);

        $recapEmployees = Attendance::query()
            ->select('employee_id')
            ->whereMonth('attendance_date', request('month', now()->month))
            ->whereYear('attendance_date', request('year', now()->year))
            ->groupBy('employee_id')
            ->with('employee')
            ->paginate(10, ['*'], 'recap_page')
            ->withQueryString();

        $monthlyRecap = Attendance::selectRaw('employee_id, status, count(*) as total')
            ->whereMonth('attendance_date', $recapMonth)
            ->whereYear('attendance_date', $recapYear)
            ->whereIn('employee_id', $recapEmployees->pluck('employee_id'))
            ->groupBy('employee_id', 'status')
            ->get()
            ->groupBy('employee_id');

        return view('hr.attendances.index', [
            'attendances' => $attendances,
            'recapEmployees' => $recapEmployees,
            'monthlyRecap' => $monthlyRecap,
            'employees' => Employee::orderBy('nama')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create()
    {
        return view('hr.attendances.form', ['attendance' => new Attendance(), 'employees' => Employee::orderBy('nama')->get(), 'statuses' => $this->statuses()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedAttendance($request);
        $validated['created_by'] = $request->user()?->id;

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('hr/attendances/photos', 'public');
        }

        Attendance::create($validated);

        return redirect()->route('hr.attendances.index')->with('success', 'Absensi berhasil ditambahkan.');
    }

    public function edit(Attendance $attendance)
    {
        return view('hr.attendances.form', ['attendance' => $attendance, 'employees' => Employee::orderBy('nama')->get(), 'statuses' => $this->statuses()]);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $validated = $this->validatedAttendance($request);

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($attendance->photo);
            $validated['photo'] = $request->file('photo')->store('hr/attendances/photos', 'public');
        }

        $attendance->update($validated);

        return redirect()->route('hr.attendances.index')->with('success', 'Absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        Storage::disk('public')->delete($attendance->photo);
        $attendance->delete();

        return back()->with('success', 'Absensi berhasil dihapus.');
    }

    private function statuses(): array
    {
        return ['hadir', 'izin', 'sakit', 'alpha', 'cuti'];
    }

    private function validatedAttendance(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i'],
            'status' => ['required', 'in:' . implode(',', $this->statuses())],
            'note' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);
    }
}
