<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Salary;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index()
    {
        $salaries = Salary::with('employee')
            ->when(request('employee_id'), fn ($query, $employeeId) => $query->where('employee_id', $employeeId))
            ->when(request('salary_period'), fn ($query, $period) => $query->where('salary_period', $period))
            ->when(request('payment_status'), fn ($query, $status) => $query->where('payment_status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.salaries.index', ['salaries' => $salaries, 'employees' => Employee::orderBy('nama')->get(), 'statuses' => $this->statuses()]);
    }

    public function create()
    {
        return view('hr.salaries.form', ['salary' => new Salary(), 'employees' => Employee::orderBy('nama')->get(), 'statuses' => $this->statuses()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedSalary($request);
        $validated['total_salary'] = $this->calculateTotal($validated);
        $validated['created_by'] = $request->user()?->id;

        $salary = Salary::create($validated);

        return redirect()->route('hr.salaries.show', $salary)->with('success', 'Data penggajian berhasil ditambahkan.');
    }

    public function show(Salary $salary)
    {
        $salary->load('employee.division', 'employee.position');

        return view('hr.salaries.show', compact('salary'));
    }

    public function edit(Salary $salary)
    {
        return view('hr.salaries.form', ['salary' => $salary, 'employees' => Employee::orderBy('nama')->get(), 'statuses' => $this->statuses()]);
    }

    public function update(Request $request, Salary $salary)
    {
        $validated = $this->validatedSalary($request);
        $validated['total_salary'] = $this->calculateTotal($validated);
        $salary->update($validated);

        return redirect()->route('hr.salaries.show', $salary)->with('success', 'Data penggajian berhasil diperbarui.');
    }

    public function destroy(Salary $salary)
    {
        $salary->delete();

        return redirect()->route('hr.salaries.index')->with('success', 'Data penggajian berhasil dihapus.');
    }

    public function markPaid(Salary $salary)
    {
        $salary->update(['payment_status' => 'sudah_dibayar', 'payment_date' => now()->toDateString()]);

        return back()->with('success', 'Gaji berhasil ditandai sudah dibayar.');
    }

    public function slip(Salary $salary)
    {
        $salary->load('employee.division', 'employee.position');

        return view('hr.salaries.slip', compact('salary'));
    }

    private function statuses(): array
    {
        return ['belum_dibayar', 'sudah_dibayar'];
    }

    private function validatedSalary(Request $request): array
    {
        return $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'salary_period' => ['required', 'date_format:Y-m'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'overtime' => ['nullable', 'numeric', 'min:0'],
            'deduction' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:' . implode(',', $this->statuses())],
            'payment_date' => ['nullable', 'date'],
            'note' => ['nullable', 'string'],
        ]);
    }

    private function calculateTotal(array $data): float
    {
        return (float) $data['basic_salary']
            + (float) ($data['allowance'] ?? 0)
            + (float) ($data['overtime'] ?? 0)
            - (float) ($data['deduction'] ?? 0);
    }
}
