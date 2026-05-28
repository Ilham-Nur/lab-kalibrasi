<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Concerns\NormalizesMoneyInputs;
use App\Http\Controllers\Controller;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RecruitmentCandidateController extends Controller
{
    use NormalizesMoneyInputs;

    public function store(Request $request, RecruitmentRequest $recruitment)
    {
        $validated = $this->validatedCandidate($request);
        $validated['recruitment_request_id'] = $recruitment->id;

        if ($request->hasFile('cv_file')) {
            $validated['cv_file'] = $request->file('cv_file')->store('hr/candidates/cv', 'public');
        }

        RecruitmentCandidate::create($validated);

        return back()->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function update(Request $request, RecruitmentCandidate $candidate)
    {
        $validated = $this->validatedCandidate($request);

        if ($request->hasFile('cv_file')) {
            Storage::disk('public')->delete($candidate->cv_file);
            $validated['cv_file'] = $request->file('cv_file')->store('hr/candidates/cv', 'public');
        }

        $candidate->update($validated);

        return back()->with('success', 'Kandidat berhasil diperbarui.');
    }

    public function destroy(RecruitmentCandidate $candidate)
    {
        Storage::disk('public')->delete($candidate->cv_file);
        $candidate->delete();

        return back()->with('success', 'Kandidat berhasil dihapus.');
    }

    private function validatedCandidate(Request $request): array
    {
        $this->normalizeMoneyInputs($request, ['expected_salary']);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'last_education' => ['nullable', 'string', 'max:255'],
            'experience' => ['nullable', 'string'],
            'expected_salary' => ['nullable', 'numeric', 'min:0'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'status' => ['required', 'in:masuk,screening,interview,diterima,ditolak'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
