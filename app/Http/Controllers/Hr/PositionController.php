<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PositionController extends Controller
{
    public function index(): View
    {
        $positions = Position::query()
            ->with('division')
            ->withCount(['employees', 'jobDescriptions'])
            ->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when(request('division_id'), fn ($query, $divisionId) => $query->where('division_id', $divisionId))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('hr.positions.index', [
            'positions' => $positions,
            'divisions' => Division::orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(): View
    {
        return view('hr.positions.form', $this->formData(new Position(['status' => 'aktif'])));
    }

    public function store(Request $request): RedirectResponse
    {
        Position::create($this->validatedPosition($request));

        return redirect()->route('hr.positions.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Position $position): View
    {
        return view('hr.positions.form', $this->formData($position));
    }

    public function update(Request $request, Position $position): RedirectResponse
    {
        $position->update($this->validatedPosition($request, $position));

        return redirect()->route('hr.positions.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Position $position): RedirectResponse
    {
        if ($position->employees()->exists() || $position->jobDescriptions()->exists()) {
            return back()->with('error', 'Jabatan tidak bisa dihapus karena masih digunakan oleh karyawan atau jobdesk.');
        }

        $position->delete();

        return redirect()->route('hr.positions.index')->with('success', 'Jabatan berhasil dihapus.');
    }

    private function formData(Position $position): array
    {
        return [
            'position' => $position,
            'divisions' => Division::orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ];
    }

    private function validatedPosition(Request $request, ?Position $position = null): array
    {
        return $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions', 'name')
                    ->where('division_id', $request->input('division_id'))
                    ->ignore($position),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:' . implode(',', $this->statuses())],
        ]);
    }

    private function statuses(): array
    {
        return ['aktif', 'nonaktif'];
    }
}
