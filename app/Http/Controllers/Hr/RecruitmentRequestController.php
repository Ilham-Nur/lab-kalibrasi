<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Position;
use App\Models\RecruitmentRequest;
use Illuminate\Http\Request;

class RecruitmentRequestController extends Controller
{
    public function index()
    {
        $recruitments = RecruitmentRequest::with(['division', 'position'])
            ->when(request('search'), fn ($query, $search) => $query->where('request_number', 'like', "%{$search}%")->orWhere('reason', 'like', "%{$search}%"))
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('hr.recruitments.index', ['recruitments' => $recruitments, 'statuses' => $this->statuses()]);
    }

    public function create()
    {
        return view('hr.recruitments.form', $this->formData(new RecruitmentRequest()));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedRecruitment($request);
        $validated['request_number'] = $this->nextRequestNumber();
        $validated['requested_by'] = $request->user()?->id;

        $recruitment = RecruitmentRequest::create($validated);

        return redirect()->route('hr.recruitments.show', $recruitment)->with('success', 'Request recruitment berhasil dibuat.');
    }

    public function show(RecruitmentRequest $recruitment)
    {
        $recruitment->load(['division', 'position']);
        $candidates = $recruitment->candidates()->latest()->paginate(5)->withQueryString();

        return view('hr.recruitments.show', ['recruitment' => $recruitment, 'candidates' => $candidates, 'candidateStatuses' => ['masuk', 'screening', 'interview', 'diterima', 'ditolak']]);
    }

    public function edit(RecruitmentRequest $recruitment)
    {
        return view('hr.recruitments.form', $this->formData($recruitment));
    }

    public function update(Request $request, RecruitmentRequest $recruitment)
    {
        $recruitment->update($this->validatedRecruitment($request));

        return redirect()->route('hr.recruitments.show', $recruitment)->with('success', 'Request recruitment berhasil diperbarui.');
    }

    public function destroy(RecruitmentRequest $recruitment)
    {
        $recruitment->delete();

        return redirect()->route('hr.recruitments.index')->with('success', 'Request recruitment berhasil dihapus.');
    }

    public function updateStatus(Request $request, RecruitmentRequest $recruitment)
    {
        $validated = $request->validate(['status' => ['required', 'in:' . implode(',', $this->statuses())]]);
        $recruitment->update($validated);

        return back()->with('success', 'Status recruitment berhasil diperbarui.');
    }

    private function formData(RecruitmentRequest $recruitment): array
    {
        return [
            'recruitment' => $recruitment,
            'divisions' => Division::where('status', 'aktif')->orderBy('name')->get(),
            'positions' => Position::where('status', 'aktif')->orderBy('name')->get(),
            'statuses' => $this->statuses(),
            'employmentTypes' => ['tetap', 'kontrak', 'freelance', 'magang'],
        ];
    }

    private function validatedRecruitment(Request $request): array
    {
        return $request->validate([
            'division_id' => ['nullable', 'exists:divisions,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'needed_count' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string'],
            'employment_type' => ['required', 'in:tetap,kontrak,freelance,magang'],
            'request_date' => ['required', 'date'],
            'status' => ['required', 'in:' . implode(',', $this->statuses())],
            'description' => ['nullable', 'string'],
        ]);
    }

    private function statuses(): array
    {
        return ['draft', 'dibuka', 'proses', 'diterima', 'ditutup', 'dibatalkan'];
    }

    private function nextRequestNumber(): string
    {
        $year = now()->format('Y');
        $last = RecruitmentRequest::whereYear('created_at', $year)->count() + 1;

        return 'HR-REC-' . $year . '-' . str_pad((string) $last, 4, '0', STR_PAD_LEFT);
    }
}
