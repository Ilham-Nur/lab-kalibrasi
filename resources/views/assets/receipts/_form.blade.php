<div class="card">
  <form method="POST" action="{{ $receipt->exists ? route('assets.receipts.update', $receipt) : route('assets.receipts.store') }}" enctype="multipart/form-data">
    @csrf
    @if($receipt->exists)
      @method('PUT')
    @endif

    <div class="modal-body">
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Procurement <span class="required">*</span></label>
          <select class="form-select" name="procurement_id" id="procurement-select" required>
            <option value="">Pilih procurement</option>
            @foreach($procurements as $item)
              <option value="{{ $item->id }}" @selected(old('procurement_id', $receipt->procurement_id ?? $procurement?->id) == $item->id)>{{ $item->procurement_number }}{{ $item->supplier ? ' - '.$item->supplier->name : '' }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Supplier / Vendor</label>
          <input class="form-control" value="{{ $procurement?->supplier?->name ?? $receipt->supplier?->name ?? $receipt->supplier_name ?? '-' }}" readonly>
        </div>
        <div class="form-group">
          <label class="form-label">Tanggal Terima</label>
          <input class="form-control" type="date" name="received_date" value="{{ old('received_date', $receipt->received_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Delivery Note</label>
          <input class="form-control" name="delivery_note_number" value="{{ old('delivery_note_number', $receipt->delivery_note_number) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Invoice</label>
          <input class="form-control" name="invoice_number" value="{{ old('invoice_number', $receipt->invoice_number) }}">
        </div>
        <div class="form-group">
          <label class="form-label">Lampiran Dokumen</label>
          <div class="file-upload-wrapper @error('document') has-error @enderror">
            <input type="file" id="asset-receipt-document-file" name="document" class="file-input @error('document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx" data-allowed-ext="pdf,jpg,jpeg,png,doc,docx,xls,xlsx" data-max-size="5">
            <label for="asset-receipt-document-file" class="file-label">
              <div class="file-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
              <div class="file-text">
                <span class="file-placeholder" data-default-placeholder="Klik untuk pilih dokumen atau drag &amp; drop">Klik untuk pilih dokumen atau drag &amp; drop</span>
                <span class="file-meta">PDF, gambar, Word, Excel - Maks. 5MB</span>
              </div>
            </label>
            <div class="file-preview"></div>
          </div>
          <div class="invalid-feedback file-error" @error('document') style="display:block;" @enderror>@error('document') {{ $message }} @enderror</div>
        </div>
        <div class="form-group">
          <label class="form-label">Catatan Dokumen</label>
          <input class="form-control" name="document_notes" value="{{ old('document_notes') }}">
        </div>
        <div class="form-group form-group-full">
          <label class="form-label">Catatan Penerimaan</label>
          <textarea class="form-textarea" name="notes">{{ old('notes', $receipt->notes) }}</textarea>
        </div>
      </div>
    </div>

    <div class="card-header"><h2 class="card-title">Item Penerimaan</h2></div>
    <div class="table-responsive receipt-item-table-wrap">
      <table class="data-table receipt-item-table">
        <thead>
          <tr>
            <th>Item</th>
            <th>Qty Approval</th>
            <th>Sudah Diterima</th>
            <th>Sisa</th>
            <th>Diterima Sekarang</th>
            <th>Kondisi</th>
            <th>Detail Aset</th>
          </tr>
        </thead>
        <tbody>
          @php($rows = old('items', $items->toArray()))
          @forelse($rows as $i => $item)
            <tr>
              <td>
                <input type="hidden" name="items[{{ $i }}][procurement_item_id]" value="{{ $item['procurement_item_id'] ?? '' }}">
                <input type="hidden" name="items[{{ $i }}][quantity_ordered]" value="{{ $item['quantity_ordered'] ?? 0 }}">
                <input type="hidden" name="items[{{ $i }}][item_name]" value="{{ $item['item_name'] ?? '' }}">
                <strong>{{ $item['item_name'] ?? '-' }}</strong>
                <div class="td-email-sub">{{ str($item['specification'] ?? '')->limit(70) }}</div>
              </td>
              <td>{{ format_qty($item['quantity_ordered'] ?? 0) }}</td>
              <td>{{ format_qty($item['previous_received'] ?? 0) }}</td>
              <td>{{ format_qty($item['remaining_quantity'] ?? $item['quantity_ordered'] ?? 0) }}</td>
              <td><input class="form-control receipt-qty-input" type="number" step="0.01" min="0" name="items[{{ $i }}][quantity_received]" value="{{ $item['quantity_received'] ?? 0 }}" required></td>
              <td>
                <select class="form-select" name="items[{{ $i }}][condition]">
                  @foreach(['good','minor_damage','damaged','under_repair','unknown'] as $condition)
                    <option value="{{ $condition }}" @selected(($item['condition'] ?? 'good') === $condition)>{{ str_replace('_', ' ', ucfirst($condition)) }}</option>
                  @endforeach
                </select>
              </td>
              <td>
                <button class="btn btn-outline btn-sm" type="button" data-receipt-detail-toggle="receipt-detail-{{ $i }}"><i class="bi bi-card-list"></i> Detail</button>
                @if(!empty($item['is_prefilled_from_previous']))
                  <div class="td-email-sub">Default dari penerimaan sebelumnya</div>
                @endif
              </td>
            </tr>
            <tr class="receipt-detail-row" id="receipt-detail-{{ $i }}">
              <td colspan="7">
                <div class="receipt-detail-panel">
                  @if(!empty($item['is_prefilled_from_previous']))
                    <div class="receipt-detail-note">Detail aset otomatis mengikuti penerimaan terakhir untuk item yang sama. Serial number tetap dikosongkan.</div>
                  @endif
                  <div class="form-grid">
                    <div class="form-group">
                      <label class="form-label">Kategori Aset</label>
                      <select class="form-select" name="items[{{ $i }}][asset_category_id]">
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                          <option value="{{ $category->id }}" @selected(($item['asset_category_id'] ?? null) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Lokasi Aset</label>
                      <select class="form-select" name="items[{{ $i }}][asset_location_id]">
                        <option value="">Pilih lokasi</option>
                        @foreach($locations as $location)
                          <option value="{{ $location->id }}" @selected(($item['asset_location_id'] ?? null) == $location->id)>{{ $location->name }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label">Merek</label>
                      <input class="form-control" name="items[{{ $i }}][brand]" value="{{ $item['brand'] ?? '' }}">
                    </div>
                    <div class="form-group">
                      <label class="form-label">Model</label>
                      <input class="form-control" name="items[{{ $i }}][model]" value="{{ $item['model'] ?? '' }}">
                    </div>
                    <div class="form-group">
                      <label class="form-label">Serial Number</label>
                      <input class="form-control" name="items[{{ $i }}][serial_number]" value="{{ $item['serial_number'] ?? '' }}">
                    </div>
                    <div class="form-group">
                      <label class="form-label">Nilai Perolehan</label>
                      <input class="form-control js-money-input" type="text" inputmode="numeric" name="items[{{ $i }}][acquisition_value]" value="{{ $item['acquisition_value'] ?? '' }}">
                    </div>
                    <div class="form-group form-group-full">
                      <label class="form-label">Spesifikasi</label>
                      <textarea class="form-textarea" name="items[{{ $i }}][specification]" rows="2">{{ $item['specification'] ?? '' }}</textarea>
                    </div>
                    <div class="form-group form-group-full">
                      <label class="form-label">Catatan Item</label>
                      <textarea class="form-textarea" name="items[{{ $i }}][notes]" rows="2">{{ $item['notes'] ?? '' }}</textarea>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7">
                <div class="empty-state">
                  <div class="empty-title">{{ $procurement ? 'Semua item pada procurement ini sudah diterima penuh' : 'Pilih procurement untuk memuat item' }}</div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="modal-footer">
      <a class="btn btn-outline" href="{{ route('assets.receipts.index') }}">Batal</a>
      <button class="btn btn-primary">Simpan</button>
    </div>
  </form>
</div>

@push('styles')
  <style>
    .receipt-item-table-wrap {
      overflow-x: auto;
    }
    .receipt-item-table {
      min-width: 980px;
    }
    .receipt-qty-input {
      min-width: 110px;
    }
    .receipt-detail-row {
      display: none;
      background: var(--content-bg);
    }
    .receipt-detail-row.is-open {
      display: table-row;
    }
    .receipt-detail-panel {
      padding: 14px;
      border-top: 1px solid var(--card-border);
    }
    .receipt-detail-note {
      margin-bottom: 12px;
      padding: 10px 12px;
      border-radius: 8px;
      background: var(--info-bg);
      color: var(--info-text);
      font-size: 12.5px;
      font-weight: 600;
    }
  </style>
@endpush

@push('scripts')
<script>
  document.getElementById('procurement-select')?.addEventListener('change', function () {
    if (this.value) window.location.href = '{{ route('assets.receipts.create') }}?procurement_id=' + this.value;
  });

  document.addEventListener('click', function (event) {
    var toggle = event.target.closest('[data-receipt-detail-toggle]');
    if (!toggle) return;

    document.getElementById(toggle.dataset.receiptDetailToggle)?.classList.toggle('is-open');
  });
</script>
@endpush
