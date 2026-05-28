@extends('layouts.app')

@section('title', 'Detail Pengadaan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $procurement->procurement_number }}</h1>
      <p class="page-subtitle">Status {{ str_replace('_', ' ', ucfirst($procurement->status)) }}</p>
    </div>
    <div class="action-btns">
      <a class="btn btn-outline btn-sm" href="{{ route('assets.procurements.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
      @if ($procurement->status === 'draft')
        <form method="POST" action="{{ route('assets.procurements.submit', $procurement) }}">
          @csrf
          <button class="btn btn-primary btn-sm"><i class="bi bi-send-fill"></i> Submit Approval</button>
        </form>
      @endif
    </div>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="card-header"><h2 class="card-title">Data Pengadaan</h2></div>
    <div class="modal-body">
      <div class="detail-grid">
        <div class="detail-item"><div class="detail-label">Request Date</div><div class="detail-value">{{ $procurement->request_date?->format('d M Y') }}</div></div>
        <div class="detail-item"><div class="detail-label">Department</div><div class="detail-value">{{ $procurement->department ?: '-' }}</div></div>
        <div class="detail-item"><div class="detail-label">Requested By</div><div class="detail-value">{{ $procurement->requestedBy?->name ?? '-' }}</div></div>
        <div class="detail-item"><div class="detail-label">Total</div><div class="detail-value">Rp {{ number_format($procurement->total_estimated_cost, 0, ',', '.') }}</div></div>
        <div class="detail-item"><div class="detail-label">Purpose</div><div class="detail-value">{{ $procurement->purpose ?: '-' }}</div></div>
        <div class="detail-item"><div class="detail-label">Notes</div><div class="detail-value">{{ $procurement->notes ?: '-' }}</div></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2 class="card-title">Item</h2></div>
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
  </div>

  <div class="card">
    <div class="card-header"><h2 class="card-title">Approval</h2></div>
    <div class="table-responsive">
      @php
        $approvalStatusLabels = [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'revision' => 'Revision',
            'skipped' => 'Tidak Dilanjutkan',
        ];
        $rejectedApprovalLevel = $procurement->approvals->firstWhere('status', 'rejected')?->approval_level;
      @endphp
      <table class="data-table">
        <thead><tr><th>Level</th><th>Role</th><th>Status</th><th>Oleh</th><th>Catatan / Alasan</th></tr></thead>
        <tbody>
          @forelse ($procurement->approvals as $approval)
            @php
              $displayStatus = $rejectedApprovalLevel && $approval->approval_level > $rejectedApprovalLevel && $approval->status === 'pending'
                  ? 'skipped'
                  : $approval->status;
            @endphp
            <tr>
              <td>{{ $approval->approval_level }}</td>
              <td><span class="badge badge-info">{{ $approval->role_name }}</span></td>
              <td><span class="status-badge status-{{ $displayStatus }}">{{ $approvalStatusLabels[$displayStatus] ?? str_replace('_', ' ', ucfirst($displayStatus)) }}</span></td>
              <td>
                {{ $approval->approvedBy?->name ?? (in_array($displayStatus, ['rejected', 'revision'], true) ? 'Belum tercatat' : '-') }}
                <div class="td-email-sub">{{ $approval->approved_at?->format('d M Y H:i') }}</div>
              </td>
              <td>{{ $approval->notes ?: '-' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="5"><div class="empty-state"><div class="empty-title">Approval dibuat saat pengadaan disubmit</div></div></td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <h2 class="card-title">Penerimaan</h2>
      @if (in_array($procurement->status, ['approved', 'purchasing'], true))
        <a class="btn btn-primary btn-sm" href="{{ route('assets.receipts.create', ['procurement_id' => $procurement->id]) }}">Buat Penerimaan</a>
      @endif
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <tbody>
          @forelse ($procurement->receipts as $receipt)
            <tr>
              <td><a href="{{ route('assets.receipts.show', $receipt) }}">{{ $receipt->receipt_number }}</a></td>
              <td>{{ $receipt->received_date?->format('d M Y') }}</td>
              <td><span class="status-badge status-{{ $receipt->status }}">{{ str_replace('_', ' ', ucfirst($receipt->status)) }}</span></td>
            </tr>
          @empty
            <tr><td><div class="empty-state"><div class="empty-title">Belum ada penerimaan</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
