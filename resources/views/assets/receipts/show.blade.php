@extends('layouts.app')

@section('title', 'Detail Penerimaan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $receipt->receipt_number }}</h1>
      <p class="page-subtitle">{{ $receipt->procurement?->procurement_number }} - {{ str_replace('_', ' ', ucfirst($receipt->status)) }}</p>
    </div>
    <div class="action-btns">
      <a class="btn btn-outline btn-sm" href="{{ route('assets.receipts.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
      @if ($receipt->items->contains(fn ($item) => ! $item->is_converted_to_asset && (float) $item->quantity_received > 0))
        <a class="btn btn-primary btn-sm" href="{{ route('assets.convert.show', $receipt) }}">Convert ke Aset</a>
      @endif
    </div>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="modal-body">
      <div class="detail-grid">
        <div class="detail-item"><div class="detail-label">Tanggal</div><div class="detail-value">{{ $receipt->received_date?->format('d M Y') }}</div></div>
        <div class="detail-item"><div class="detail-label">Supplier</div><div class="detail-value">{{ $receipt->supplier?->name ?? $receipt->supplier_name ?? '-' }}</div></div>
        <div class="detail-item"><div class="detail-label">Delivery Note</div><div class="detail-value">{{ $receipt->delivery_note_number ?: '-' }}</div></div>
        <div class="detail-item"><div class="detail-label">Invoice</div><div class="detail-value">{{ $receipt->invoice_number ?: '-' }}</div></div>
        <div class="detail-item"><div class="detail-label">Received By</div><div class="detail-value">{{ $receipt->receivedBy?->name ?? '-' }}</div></div>
        <div class="detail-item"><div class="detail-label">Notes</div><div class="detail-value">{{ $receipt->notes ?: '-' }}</div></div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2 class="card-title">Item Diterima</h2></div>
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Item</th><th>Qty Approval</th><th>Received</th><th>Calon Data Aset</th><th>Kondisi</th><th>Converted</th></tr></thead>
        <tbody>
          @foreach($receipt->items as $item)
            <tr>
              <td>{{ $item->item_name }}<div class="td-email-sub">{{ $item->notes }}</div></td>
              <td>{{ format_qty($item->quantity_ordered) }}</td>
              <td>{{ format_qty($item->quantity_received) }}</td>
              <td>
                <strong>{{ trim(($item->brand ?? '').' '.($item->model ?? '')) ?: '-' }}</strong>
                <div class="td-email-sub">SN: {{ $item->serial_number ?: '-' }}</div>
                <div class="td-email-sub">{{ $item->category?->name ?? '-' }} / {{ $item->location?->name ?? '-' }}</div>
              </td>
              <td>{{ str_replace('_', ' ', ucfirst($item->condition ?? '-')) }}</td>
              <td>{{ $item->is_converted_to_asset ? 'Ya' : 'Belum' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h2 class="card-title">Lampiran Dokumen</h2></div>
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>File</th><th>Catatan</th><th>Upload By</th><th>Tanggal</th></tr></thead>
        <tbody>
          @forelse($receipt->documents as $document)
            <tr>
              <td><a href="{{ $document->file_url }}" target="_blank">{{ $document->file_name }}</a></td>
              <td>{{ $document->notes ?: '-' }}</td>
              <td>{{ $document->uploadedBy?->name ?? '-' }}</td>
              <td>{{ $document->created_at?->format('d M Y H:i') }}</td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty-state"><div class="empty-title">Belum ada lampiran</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection
