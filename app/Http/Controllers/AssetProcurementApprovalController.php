<?php

namespace App\Http\Controllers;

use App\Models\AssetProcurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetProcurementApprovalController extends Controller
{
    public function index()
    {
        $procurements = AssetProcurement::with(['requestedBy', 'supplier', 'items', 'approvals.approvedBy'])
            ->whereIn('status', ['waiting_supervisor', 'waiting_finance', 'waiting_director'])
            ->oldest('request_date')
            ->get();

        return view('assets.procurements.approvals', [
            'approvalColumns' => [
                [
                    'level' => 1,
                    'role' => 'Supervisor',
                    'status' => 'waiting_supervisor',
                    'icon' => 'bi-person-check-fill',
                    'items' => $procurements->where('current_approval_level', 1)->values(),
                ],
                [
                    'level' => 2,
                    'role' => 'Keuangan',
                    'status' => 'waiting_finance',
                    'icon' => 'bi-wallet2',
                    'items' => $procurements->where('current_approval_level', 2)->values(),
                ],
                [
                    'level' => 3,
                    'role' => 'Direktur',
                    'status' => 'waiting_director',
                    'icon' => 'bi-award-fill',
                    'items' => $procurements->where('current_approval_level', 3)->values(),
                ],
            ],
        ]);
    }

    public function show(AssetProcurement $procurement)
    {
        return view('assets.procurements.show', [
            'procurement' => $procurement->load(['requestedBy', 'items', 'approvals.approvedBy', 'receipts.items']),
        ]);
    }

    public function approve(Request $request, AssetProcurement $procurement)
    {
        return $this->decide($request, $procurement, 'approved');
    }

    public function reject(Request $request, AssetProcurement $procurement)
    {
        $request->validate(['notes' => ['required', 'string']]);

        return $this->decide($request, $procurement, 'rejected');
    }

    public function revision(Request $request, AssetProcurement $procurement)
    {
        $request->validate(['notes' => ['required', 'string']]);

        return $this->decide($request, $procurement, 'revision');
    }

    private function decide(Request $request, AssetProcurement $procurement, string $decision)
    {
        $level = (int) $procurement->current_approval_level;
        $approval = $procurement->approvals()->where('approval_level', $level)->first();

        if (! $approval || ! in_array($procurement->status, ['waiting_supervisor', 'waiting_finance', 'waiting_director'], true)) {
            return back()->with('error', 'Pengadaan tidak sedang menunggu approval.');
        }

        if (! $this->canApprove($request, $approval->role_name)) {
            return back()->with('error', "User tidak sesuai untuk approval {$approval->role_name}.");
        }

        if ($level > 1 && $procurement->approvals()->where('approval_level', $level - 1)->where('status', 'approved')->doesntExist()) {
            return back()->with('error', 'Approval sebelumnya belum selesai.');
        }

        DB::transaction(function () use ($request, $procurement, $approval, $decision, $level) {
            $approval->update([
                'status' => $decision,
                'notes' => $request->input('notes'),
                'approved_by' => $request->user()?->id,
                'approved_at' => now(),
            ]);

            if ($decision === 'rejected') {
                $this->skipNextApprovals($procurement, $level);

                $procurement->update($this->rejectedProcurementUpdates($level));
                return;
            }

            if ($decision === 'revision') {
                $procurement->update(['status' => 'draft', 'current_approval_level' => null, $this->statusColumn($level) => 'revision']);
                return;
            }

            $updates = [$this->statusColumn($level) => 'approved'];
            if ($level === 1) {
                $updates += ['status' => 'waiting_finance', 'current_approval_level' => 2];
            } elseif ($level === 2) {
                $updates += ['status' => 'waiting_director', 'current_approval_level' => 3];
            } else {
                $updates += ['status' => 'approved', 'current_approval_level' => null];
            }

            $procurement->update($updates);
        });

        return redirect()->route('assets.procurements.show', $procurement)->with('success', 'Keputusan approval berhasil disimpan.');
    }

    private function statusColumn(int $level): string
    {
        return [1 => 'supervisor_status', 2 => 'finance_status', 3 => 'director_status'][$level] ?? 'supervisor_status';
    }

    private function rejectedProcurementUpdates(int $level): array
    {
        $updates = [
            'status' => 'rejected',
            'current_approval_level' => null,
            $this->statusColumn($level) => 'rejected',
        ];

        for ($nextLevel = $level + 1; $nextLevel <= 3; $nextLevel++) {
            $updates[$this->statusColumn($nextLevel)] = 'skipped';
        }

        return $updates;
    }

    private function skipNextApprovals(AssetProcurement $procurement, int $level): void
    {
        $procurement->approvals()
            ->where('approval_level', '>', $level)
            ->where('status', 'pending')
            ->update([
                'status' => 'skipped',
                'notes' => 'Tidak dilanjutkan karena approval sebelumnya ditolak.',
            ]);
    }

    private function canApprove(Request $request, string $role): bool
    {
        $user = $request->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole($role) || $user->hasRole('Admin');
    }
}
