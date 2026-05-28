<div class="card">
  <form method="POST" action="{{ $receipt->exists ? route('assets.receipts.update',$receipt) : route('assets.receipts.store') }}">
    @csrf @if($receipt->exists) @method('PUT') @endif
    <div class="modal-body"><div class="form-grid">
      <div class="form-group"><label class="form-label">Procurement <span class="required">*</span></label><select class="form-select" name="procurement_id" id="procurement-select" required><option value="">Pilih procurement</option>@foreach($procurements as $item)<option value="{{ $item->id }}" @selected(old('procurement_id',$receipt->procurement_id ?? $procurement?->id)==$item->id)>{{ $item->procurement_number }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Tanggal Terima</label><input class="form-control" type="date" name="received_date" value="{{ old('received_date',$receipt->received_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
      <div class="form-group"><label class="form-label">Supplier</label><input class="form-control" name="supplier_name" value="{{ old('supplier_name',$receipt->supplier_name) }}"></div>
      <div class="form-group"><label class="form-label">Delivery Note</label><input class="form-control" name="delivery_note_number" value="{{ old('delivery_note_number',$receipt->delivery_note_number) }}"></div>
      <div class="form-group"><label class="form-label">Invoice</label><input class="form-control" name="invoice_number" value="{{ old('invoice_number',$receipt->invoice_number) }}"></div>
      <div class="form-group form-group-full"><label class="form-label">Catatan</label><textarea class="form-textarea" name="notes">{{ old('notes',$receipt->notes) }}</textarea></div>
    </div></div>
    <div class="card-header"><h2 class="card-title">Item Penerimaan</h2></div>
    <div class="table-responsive"><table class="data-table"><thead><tr><th>Item</th><th>Ordered</th><th>Received</th><th>Kondisi</th><th>Catatan</th></tr></thead><tbody>
      @php($rows = old('items', $items->count() ? $items->map(fn($item) => [
        'procurement_item_id' => $item->procurement_item_id ?? $item->id,
        'item_name' => $item->item_name,
        'quantity_ordered' => $item->quantity_ordered ?? $item->quantity,
        'quantity_received' => $item->quantity_received ?? 0,
        'condition' => $item->condition ?? 'good',
        'notes' => $item->notes ?? '',
      ])->toArray() : []))
      @forelse($rows as $i => $item)
        <tr><td><input type="hidden" name="items[{{ $i }}][procurement_item_id]" value="{{ $item['procurement_item_id'] ?? '' }}"><input class="form-control" name="items[{{ $i }}][item_name]" value="{{ $item['item_name'] ?? '' }}" required></td><td><input class="form-control" type="number" step="0.01" name="items[{{ $i }}][quantity_ordered]" value="{{ $item['quantity_ordered'] ?? 0 }}" required></td><td><input class="form-control" type="number" step="0.01" min="0" name="items[{{ $i }}][quantity_received]" value="{{ $item['quantity_received'] ?? 0 }}" required></td><td><select class="form-select" name="items[{{ $i }}][condition]">@foreach(['good','minor_damage','damaged','under_repair','unknown'] as $condition)<option value="{{ $condition }}" @selected(($item['condition'] ?? 'good')===$condition)>{{ str_replace('_',' ',ucfirst($condition)) }}</option>@endforeach</select></td><td><textarea class="form-textarea" name="items[{{ $i }}][notes]" rows="1">{{ $item['notes'] ?? '' }}</textarea></td></tr>
      @empty
        <tr><td colspan="5"><div class="empty-state"><div class="empty-title">Pilih procurement untuk memuat item</div></div></td></tr>
      @endforelse
    </tbody></table></div>
    <div class="modal-footer"><a class="btn btn-outline" href="{{ route('assets.receipts.index') }}">Batal</a><button class="btn btn-primary">Simpan</button></div>
  </form>
</div>
@push('scripts')
<script>
  document.getElementById('procurement-select')?.addEventListener('change', function () {
    if (this.value) window.location.href = '{{ route('assets.receipts.create') }}?procurement_id=' + this.value;
  });
</script>
@endpush
