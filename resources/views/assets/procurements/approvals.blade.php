@extends('layouts.app')

@section('title', 'Approval Pengadaan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Approval Pengadaan</h1>
      <p class="page-subtitle">Antrian pengadaan berdasarkan level approval berurutan.</p>
    </div>
  </div>

  @include('hr.partials.alerts')
  @include('assets.procurements._tabs')

  <div class="approval-board">
    @foreach ($approvalColumns as $column)
      <section class="approval-lane approval-lane-{{ $column['level'] }}">
        <div class="approval-lane-header">
          <div class="approval-lane-icon"><i class="bi {{ $column['icon'] }}"></i></div>
          <div>
            <h2>{{ $column['role'] }}</h2>
            <p>Level {{ $column['level'] }} approval</p>
          </div>
          <span class="approval-lane-count">{{ $column['items']->count() }}</span>
        </div>

        <div class="approval-ticket-list">
          @forelse ($column['items'] as $procurement)
            @php
              $currentApproval = $procurement->approvals->firstWhere('approval_level', $procurement->current_approval_level);
              $totalItems = $procurement->items->count();
              $totalQuantity = $procurement->items->sum('quantity');
              $waitingDays = $procurement->request_date ? (int) floor($procurement->request_date->diffInDays(now())) : 0;
              $waitingLabel = $waitingDays > 0 ? "Menunggu {$waitingDays} hari" : 'Menunggu hari ini';
              $canDecide = auth()->user()?->hasRole($column['role']) || auth()->user()?->hasRole('Admin');
            @endphp
            <button class="approval-ticket" type="button" data-open-modal="modal-approval-{{ $procurement->id }}">
              <span class="approval-ticket-accent"></span>
              <div class="approval-ticket-top">
                <span class="approval-number">{{ $procurement->procurement_number }}</span>
                <span class="approval-chip">{{ str_replace('_', ' ', ucfirst($procurement->status)) }}</span>
              </div>
              <div class="approval-ticket-title">{{ str($procurement->purpose ?: 'Pengajuan pengadaan')->limit(72) }}</div>
              <div class="approval-ticket-meta">
                <span><i class="bi bi-person"></i> {{ $procurement->requestedBy?->name ?? '-' }}</span>
                <span><i class="bi bi-building"></i> {{ $procurement->department ?: '-' }}</span>
              </div>
              <div class="approval-ticket-footer">
                <span>{{ $totalItems }} item / {{ number_format($totalQuantity, 0, ',', '.') }} qty</span>
                <strong>Rp {{ number_format($procurement->total_estimated_cost, 0, ',', '.') }}</strong>
              </div>
              <div class="approval-ticket-aging">
                <i class="bi bi-clock-history"></i>
                {{ $waitingLabel }}
                @unless ($canDecide)
                  <span class="badge badge-secondary">Role {{ $column['role'] }}</span>
                @endunless
              </div>
            </button>
          @empty
            <div class="approval-empty">
              <i class="bi bi-check2-circle"></i>
              <strong>Tidak ada antrian</strong>
              <span>Belum ada pengadaan di level ini.</span>
            </div>
          @endforelse
        </div>
      </section>
    @endforeach
  </div>

  @foreach ($approvalColumns as $column)
    @foreach ($column['items'] as $procurement)
      @php
        $currentApproval = $procurement->approvals->firstWhere('approval_level', $procurement->current_approval_level);
        $canDecide = auth()->user()?->hasRole($column['role']) || auth()->user()?->hasRole('Admin');
        $approvalStatusLabels = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'revision' => 'Revision',
            'skipped' => 'Tidak Dilanjutkan',
        ];
        $rejectedApprovalLevel = $procurement->approvals->firstWhere('status', 'rejected')?->approval_level;
      @endphp
      <div class="modal-overlay" id="modal-approval-{{ $procurement->id }}" aria-hidden="true">
        <div class="modal modal-xl" role="dialog" aria-modal="true" aria-labelledby="modal-approval-title-{{ $procurement->id }}">
          <div class="modal-header">
            <h3 class="modal-title" id="modal-approval-title-{{ $procurement->id }}">
              <i class="bi {{ $column['icon'] }}"></i> {{ $procurement->procurement_number }}
            </h3>
            <button class="modal-close" data-close-modal="modal-approval-{{ $procurement->id }}" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
          </div>

          <div class="modal-body">
            <div class="approval-modal-summary">
              <div>
                <span class="detail-label">Approval Saat Ini</span>
                <strong>{{ $currentApproval?->role_name ?? $column['role'] }}</strong>
              </div>
              <div>
                <span class="detail-label">Status</span>
                <span class="status-badge status-pending">{{ str_replace('_', ' ', ucfirst($procurement->status)) }}</span>
              </div>
              <div>
                <span class="detail-label">Total Estimasi</span>
                <strong>Rp {{ number_format($procurement->total_estimated_cost, 0, ',', '.') }}</strong>
              </div>
            </div>

            <div class="detail-grid">
              <div class="detail-item"><div class="detail-label">Tanggal Request</div><div class="detail-value">{{ $procurement->request_date?->format('d M Y') }}</div></div>
              <div class="detail-item"><div class="detail-label">Department</div><div class="detail-value">{{ $procurement->department ?: '-' }}</div></div>
              <div class="detail-item"><div class="detail-label">Requester</div><div class="detail-value">{{ $procurement->requestedBy?->name ?? '-' }}</div></div>
              <div class="detail-item"><div class="detail-label">Purpose</div><div class="detail-value">{{ $procurement->purpose ?: '-' }}</div></div>
              <div class="detail-item"><div class="detail-label">Notes</div><div class="detail-value">{{ $procurement->notes ?: '-' }}</div></div>
              <div class="detail-item"><div class="detail-label">Nomor</div><div class="detail-value">{{ $procurement->procurement_number }}</div></div>
            </div>

            <h3 class="revision-modal-heading">Item Pengadaan</h3>
            <div class="table-responsive">
              <table class="data-table">
                <thead><tr><th>Item</th><th>Qty</th><th>Harga</th><th>Total</th><th>Supplier</th></tr></thead>
                <tbody>
                  @foreach ($procurement->items as $item)
                    <tr>
                      <td>{{ $item->item_name }}<div class="td-email-sub">{{ $item->specification }}</div></td>
                      <td>{{ $item->quantity }} {{ $item->unit }}</td>
                      <td>Rp {{ number_format($item->estimated_unit_price, 0, ',', '.') }}</td>
                      <td>Rp {{ number_format($item->estimated_total_price, 0, ',', '.') }}</td>
                      <td>{{ $item->supplier_candidate ?: '-' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <h3 class="revision-modal-heading">Timeline Approval</h3>
            <div class="approval-timeline">
              @foreach ($procurement->approvals->sortBy('approval_level') as $approval)
                @php
                  $displayStatus = $rejectedApprovalLevel && $approval->approval_level > $rejectedApprovalLevel && $approval->status === 'pending'
                      ? 'skipped'
                      : $approval->status;
                @endphp
                <div class="approval-timeline-item {{ $displayStatus === 'approved' ? 'is-done' : ($displayStatus === 'rejected' ? 'is-rejected' : ($displayStatus === 'skipped' ? 'is-skipped' : ($approval->approval_level === $procurement->current_approval_level ? 'is-current' : ''))) }}">
                  <span>{{ $approval->approval_level }}</span>
                  <div>
                    <strong>{{ $approval->role_name }}</strong>
                    <p>
                      <span class="status-badge status-{{ $displayStatus }}">{{ $approvalStatusLabels[$displayStatus] ?? str_replace('_', ' ', ucfirst($displayStatus)) }}</span>
                      @if($approval->approvedBy)
                        oleh {{ $approval->approvedBy->name }}
                      @elseif(in_array($displayStatus, ['rejected', 'revision'], true))
                        oleh Belum tercatat
                      @endif
                      @if($approval->approved_at)
                        pada {{ $approval->approved_at->format('d M Y H:i') }}
                      @endif
                    </p>
                    @if ($approval->notes)
                      <small>{{ $approval->notes }}</small>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>

            @can('asset-procurements.approve')
              @if ($canDecide)
                <div class="approval-decision-panels">
                  <div class="approval-decision-panel" id="reject-panel-{{ $procurement->id }}">
                    <form method="POST" action="{{ route('assets.procurements.approvals.reject', $procurement) }}">
                      @csrf
                      <label class="form-label">Catatan Reject <span class="required">*</span></label>
                      <textarea class="form-textarea" name="notes" rows="3" required></textarea>
                      <div class="modal-footer">
                        <button class="btn btn-outline" type="button" data-decision-close>Batalkan</button>
                        <button class="btn btn-primary" type="submit">Kirim Reject</button>
                      </div>
                    </form>
                  </div>
                  <div class="approval-decision-panel" id="revision-panel-{{ $procurement->id }}">
                    <form method="POST" action="{{ route('assets.procurements.approvals.revision', $procurement) }}">
                      @csrf
                      <label class="form-label">Catatan Revision <span class="required">*</span></label>
                      <textarea class="form-textarea" name="notes" rows="3" required></textarea>
                      <div class="modal-footer">
                        <button class="btn btn-outline" type="button" data-decision-close>Batalkan</button>
                        <button class="btn btn-primary" type="submit">Kirim Revision</button>
                      </div>
                    </form>
                  </div>
                </div>
              @endif
            @endcan
          </div>

          <div class="modal-footer approval-modal-actions">
            <a class="btn btn-outline" href="{{ route('assets.procurements.show', $procurement) }}">Buka Detail Penuh</a>
            <button class="btn btn-outline" type="button" data-close-modal="modal-approval-{{ $procurement->id }}">Tutup</button>
            @can('asset-procurements.approve')
              @if ($canDecide)
                <button class="btn btn-outline" type="button" data-decision-open="reject-panel-{{ $procurement->id }}">Reject</button>
                <button class="btn btn-outline" type="button" data-decision-open="revision-panel-{{ $procurement->id }}">Revision</button>
                <form method="POST" action="{{ route('assets.procurements.approvals.approve', $procurement) }}">
                  @csrf
                  <input type="hidden" name="notes" value="">
                  <button class="btn btn-primary" type="submit">Approve</button>
                </form>
              @else
                <span class="badge badge-secondary">Butuh role {{ $column['role'] }}</span>
              @endif
            @endcan
          </div>
        </div>
      </div>
    @endforeach
  @endforeach
@endsection

@push('styles')
  <style>
    .approval-board-shell {
      display: block;
    }
    .approval-board {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 18px;
      align-items: start;
    }
    .approval-lane {
      --lane-accent: var(--info-text);
      min-height: 520px;
      padding: 0;
    }
    .approval-lane-1 { --lane-accent: #0284c7; }
    .approval-lane-2 { --lane-accent: #d97706; }
    .approval-lane-3 { --lane-accent: #0f766e; }
    .approval-lane-header {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0 4px 14px;
      min-height: 50px;
    }
    .approval-lane-header h2 {
      margin: 0;
      font-size: 15.5px;
      font-weight: 700;
      color: var(--text-primary);
    }
    .approval-lane-header p {
      margin: 2px 0 0;
      font-size: 12px;
      color: var(--text-muted);
    }
    .approval-lane-icon {
      width: 36px;
      height: 36px;
      display: grid;
      place-items: center;
      border-radius: 8px;
      background: color-mix(in srgb, var(--lane-accent) 12%, white);
      color: var(--lane-accent);
      flex: 0 0 auto;
    }
    .approval-lane-count {
      margin-left: auto;
      min-width: 24px;
      height: 24px;
      display: grid;
      place-items: center;
      font-weight: 700;
      font-size: 12px;
      color: var(--text-primary);
    }
    .approval-ticket-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      padding: 10px;
      min-height: 460px;
      border-radius: 8px;
      background: rgba(148, 163, 184, 0.12);
      border: 1px solid rgba(148, 163, 184, 0.16);
    }
    .approval-ticket {
      width: 100%;
      position: relative;
      display: block;
      text-align: left;
      background: var(--card-bg);
      border-radius: 8px;
      border: 1px solid rgba(148, 163, 184, 0.24);
      padding: 12px;
      cursor: pointer;
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
      transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease, background .16s ease;
    }
    .approval-ticket:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
      border-color: color-mix(in srgb, var(--lane-accent) 38%, var(--card-border));
      background: #fff;
    }
    .approval-ticket-accent {
      display: block;
      width: 28px;
      height: 4px;
      margin-bottom: 10px;
      border-radius: 999px;
      background: var(--lane-accent);
    }
    .approval-ticket-top,
    .approval-ticket-footer,
    .approval-ticket-aging {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
    }
    .approval-ticket-top { align-items: flex-start; }
    .approval-number {
      font-family: var(--font-mono);
      font-size: 11.5px;
      font-weight: 700;
      color: var(--text-primary);
    }
    .approval-chip {
      display: inline-flex;
      align-items: center;
      max-width: 140px;
      min-height: 22px;
      padding: 3px 8px;
      border-radius: 999px;
      background: #fef3c7;
      color: #92400e;
      font-size: 11px;
      font-weight: 700;
      white-space: nowrap;
    }
    .approval-ticket-title {
      margin-top: 10px;
      min-height: 34px;
      font-size: 14px;
      font-weight: 700;
      line-height: 1.35;
      color: var(--text-primary);
    }
    .approval-ticket-meta {
      display: grid;
      gap: 6px;
      margin-top: 10px;
      font-size: 12px;
      color: var(--text-muted);
    }
    .approval-ticket-meta span {
      display: flex;
      align-items: center;
      gap: 6px;
      min-width: 0;
    }
    .approval-ticket-footer {
      margin-top: 12px;
      padding-top: 10px;
      border-top: 1px solid var(--card-border);
      font-size: 12px;
      color: var(--text-muted);
    }
    .approval-ticket-footer strong {
      color: var(--text-primary);
      white-space: nowrap;
    }
    .approval-ticket-aging {
      justify-content: flex-start;
      margin-top: 10px;
      font-size: 11.8px;
      color: var(--text-muted);
    }
    .approval-empty {
      min-height: 220px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 6px;
      color: var(--text-muted);
      text-align: center;
      padding: 18px;
      background: transparent;
    }
    .approval-empty i {
      font-size: 24px;
      color: var(--success);
    }
    .approval-empty strong {
      color: var(--text-primary);
    }
    .approval-modal-summary {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 12px;
      margin-bottom: 18px;
    }
    .approval-modal-summary > div {
      border: 1px solid var(--card-border);
      border-radius: 8px;
      padding: 12px;
      background: var(--content-bg);
    }
    .approval-modal-summary strong {
      display: block;
      margin-top: 4px;
      color: var(--text-primary);
    }
    .approval-timeline {
      display: grid;
      gap: 10px;
    }
    .approval-timeline-item {
      display: grid;
      grid-template-columns: 32px 1fr;
      gap: 10px;
      align-items: flex-start;
      padding: 10px;
      border: 1px solid var(--card-border);
      border-radius: 8px;
    }
    .approval-timeline-item > span {
      width: 28px;
      height: 28px;
      display: grid;
      place-items: center;
      border-radius: 999px;
      background: var(--content-bg);
      font-weight: 700;
      color: var(--text-muted);
    }
    .approval-timeline-item.is-done > span {
      background: var(--success-bg);
      color: var(--success-text);
    }
    .approval-timeline-item.is-current > span {
      background: var(--warning-bg);
      color: var(--warning-text);
    }
    .approval-timeline-item.is-rejected > span {
      background: var(--danger-bg);
      color: var(--danger-text);
    }
    .approval-timeline-item.is-skipped > span {
      background: #f1f3f6;
      color: var(--text-secondary);
    }
    .approval-timeline-item strong {
      color: var(--text-primary);
    }
    .approval-timeline-item p,
    .approval-timeline-item small {
      margin: 2px 0 0;
      color: var(--text-muted);
    }
    .approval-decision-panel {
      display: none;
      margin-top: 16px;
      padding: 14px;
      border: 1px solid var(--card-border);
      border-radius: 8px;
      background: var(--content-bg);
    }
    .approval-decision-panel.is-open {
      display: block;
    }
    .approval-modal-actions {
      align-items: center;
    }
    .approval-modal-actions form {
      margin: 0;
    }
    @media (max-width: 1180px) {
      .approval-board { grid-template-columns: 1fr; }
      .approval-lane { min-height: auto; }
    }
    @media (max-width: 768px) {
      .approval-modal-summary { grid-template-columns: 1fr; }
      .approval-modal-actions { flex-wrap: wrap; }
    }
  </style>
@endpush

@push('scripts')
  <script>
    (function () {
      document.addEventListener('click', function (event) {
        var openButton = event.target.closest('[data-decision-open]');
        if (openButton) {
          var modal = openButton.closest('.modal');
          modal?.querySelectorAll('.approval-decision-panel').forEach(function (panel) {
            panel.classList.remove('is-open');
          });
          document.getElementById(openButton.dataset.decisionOpen)?.classList.add('is-open');
        }

        if (event.target.closest('[data-decision-close]')) {
          event.target.closest('.approval-decision-panel')?.classList.remove('is-open');
        }
      });
    })();
  </script>
@endpush
