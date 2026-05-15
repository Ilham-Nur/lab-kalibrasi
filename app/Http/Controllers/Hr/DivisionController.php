<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DivisionController extends Controller
{
    public function index(): View
    {
        $divisions = Division::query()
            ->withCount(['positions', 'employees', 'jobDescriptions'])
            ->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('hr.divisions.index', [
            'divisions' => $divisions,
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(): View
    {
        return view('hr.divisions.form', [
            'division' => new Division(['status' => 'aktif']),
            'statuses' => $this->statuses(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Division::create($this->validatedDivision($request));

        return redirect()->route('hr.divisions.index')->with('success', 'Divisi berhasil ditambahkan.');
    }

    public function edit(Division $division): View
    {
        return view('hr.divisions.form', [
            'division' => $division,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Division $division): RedirectResponse
    {
        $division->update($this->validatedDivision($request, $division));

        return redirect()->route('hr.divisions.index')->with('success', 'Divisi berhasil diperbarui.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        if ($division->positions()->exists() || $division->employees()->exists() || $division->jobDescriptions()->exists()) {
            return back()->with('error', 'Divisi tidak bisa dihapus karena masih digunakan oleh jabatan, karyawan, atau jobdesk.');
        }

        $division->delete();

        return redirect()->route('hr.divisions.index')->with('success', 'Divisi berhasil dihapus.');
    }

    private function validatedDivision(Request $request, ?Division $division = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('divisions', 'name')->ignore($division)],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', $this->statuses())],
        ]);
    }

    private function statuses(): array
    {
        return ['aktif', 'nonaktif'];
    }
}
