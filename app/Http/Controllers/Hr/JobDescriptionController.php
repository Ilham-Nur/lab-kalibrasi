<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Employee;
use App\Models\JobDescription;
use App\Models\Position;
use Illuminate\Http\Request;

class JobDescriptionController extends Controller
{
    public function index()
    {
        $jobDescriptions = JobDescription::with(['division', 'position', 'directSupervisor'])
            ->when(request('division_id'), fn ($query, $divisionId) => $query->where('division_id', $divisionId))
            ->when(request('position_id'), fn ($query, $positionId) => $query->where('position_id', $positionId))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.job-descriptions.index', $this->sharedData() + ['jobDescriptions' => $jobDescriptions]);
    }

    public function create()
    {
        return view('hr.job-descriptions.form', $this->sharedData() + ['jobDescription' => new JobDescription()]);
    }

    public function store(Request $request)
    {
        $jobDescription = JobDescription::create($this->validatedJobDescription($request));

        return redirect()->route('hr.job-descriptions.show', $jobDescription)->with('success', 'Jobdesk berhasil dibuat.');
    }

    public function show(JobDescription $jobDescription)
    {
        $jobDescription->load(['division', 'position', 'directSupervisor']);

        return view('hr.job-descriptions.show', compact('jobDescription'));
    }

    public function edit(JobDescription $jobDescription)
    {
        return view('hr.job-descriptions.form', $this->sharedData() + compact('jobDescription'));
    }

    public function update(Request $request, JobDescription $jobDescription)
    {
        $jobDescription->update($this->validatedJobDescription($request));

        return redirect()->route('hr.job-descriptions.show', $jobDescription)->with('success', 'Jobdesk berhasil diperbarui.');
    }

    public function destroy(JobDescription $jobDescription)
    {
        $jobDescription->delete();

        return redirect()->route('hr.job-descriptions.index')->with('success', 'Jobdesk berhasil dihapus.');
    }

    private function sharedData(): array
    {
        return [
            'divisions' => Division::where('status', 'aktif')->orderBy('name')->get(),
            'positions' => Position::where('status', 'aktif')->orderBy('name')->get(),
            'employees' => Employee::orderBy('nama')->get(),
            'statuses' => ['aktif', 'nonaktif'],
        ];
    }

    private function validatedJobDescription(Request $request): array
    {
        return $request->validate([
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'target_work' => ['nullable', 'string'],
            'direct_supervisor_id' => ['nullable', 'exists:employees,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);
    }
}
