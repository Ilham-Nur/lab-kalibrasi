<div class="card">
  <form method="POST" action="{{ $procurement->exists ? route('assets.procurements.update',$procurement) : route('assets.procurements.store') }}">
    @csrf @if($procurement->exists) @method('PUT') @endif
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Tanggal Request <span class="required">*</span></label><input class="form-control" type="date" name="request_date" value="{{ old('request_date', $procurement->request_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
        <div class="form-group"><label class="form-label">Department</label><input class="form-control" name="department" value="{{ old('department',$procurement->department) }}"></div>
        <div class="form-group form-group-full"><label class="form-label">Purpose</label><textarea class="form-textarea" name="purpose" rows="3">{{ old('purpose',$procurement->purpose) }}</textarea></div>
        <div class="form-group form-group-full"><label class="form-label">Catatan</label><textarea class="form-textarea" name="notes" rows="2">{{ old('notes',$procurement->notes) }}</textarea></div>
      </div>
      @error('items')<div class="invalid-feedback" style="display:block">{{ $message }}</div>@enderror
    </div>
    <div class="card-header"><h2 class="card-title">Item Pengadaan</h2><button class="btn btn-outline btn-sm" type="button" id="add-procurement-row"><i class="bi bi-plus-lg"></i> Tambah Row</button></div>
    <div class="table-responsive"><table class="data-table" id="procurement-items-table"><thead><tr><th>Item</th><th>Spesifikasi</th><th>Qty</th><th>Unit</th><th>Harga</th><th>Supplier</th><th>Alasan</th><th></th></tr></thead><tbody>
      @php($rows = old('items', $items->count() ? $items->toArray() : [['item_name'=>'','specification'=>'','quantity'=>1,'unit'=>'unit','estimated_unit_price'=>0,'supplier_candidate'=>'','reason'=>'']]))
      @foreach($rows as $i => $item)
        <tr><td><input class="form-control" name="items[{{ $i }}][item_name]" value="{{ $item['item_name'] ?? '' }}" required></td><td><textarea class="form-textarea" name="items[{{ $i }}][specification]" rows="1">{{ $item['specification'] ?? '' }}</textarea></td><td><input class="form-control" type="number" step="0.01" min="0.01" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required></td><td><input class="form-control" name="items[{{ $i }}][unit]" value="{{ $item['unit'] ?? '' }}"></td><td><input class="form-control js-money-input" type="text" inputmode="numeric" name="items[{{ $i }}][estimated_unit_price]" value="{{ $item['estimated_unit_price'] ?? 0 }}"></td><td><input class="form-control" name="items[{{ $i }}][supplier_candidate]" value="{{ $item['supplier_candidate'] ?? '' }}"></td><td><textarea class="form-textarea" name="items[{{ $i }}][reason]" rows="1">{{ $item['reason'] ?? '' }}</textarea></td><td><button class="btn-action btn-delete remove-row" type="button"><i class="bi bi-trash3-fill"></i></button></td></tr>
      @endforeach
    </tbody></table></div>
    <div class="modal-footer"><a class="btn btn-outline" href="{{ route('assets.procurements.index') }}">Batal</a><button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Simpan</button></div>
  </form>
</div>
@push('scripts')
<script>
  (function () {
    var index = document.querySelectorAll('#procurement-items-table tbody tr').length;
    document.getElementById('add-procurement-row')?.addEventListener('click', function () {
      var row = document.createElement('tr');
      row.innerHTML = '<td><input class="form-control" name="items['+index+'][item_name]" required></td><td><textarea class="form-textarea" name="items['+index+'][specification]" rows="1"></textarea></td><td><input class="form-control" type="number" step="0.01" min="0.01" name="items['+index+'][quantity]" value="1" required></td><td><input class="form-control" name="items['+index+'][unit]" value="unit"></td><td><input class="form-control js-money-input" type="text" inputmode="numeric" name="items['+index+'][estimated_unit_price]" value="0"></td><td><input class="form-control" name="items['+index+'][supplier_candidate]"></td><td><textarea class="form-textarea" name="items['+index+'][reason]" rows="1"></textarea></td><td><button class="btn-action btn-delete remove-row" type="button"><i class="bi bi-trash3-fill"></i></button></td>';
      document.querySelector('#procurement-items-table tbody').appendChild(row);
      if (window.AppMoneyInput) window.AppMoneyInput.init($(row));
      index++;
    });
    document.addEventListener('click', function (event) {
      if (event.target.closest('.remove-row') && document.querySelectorAll('#procurement-items-table tbody tr').length > 1) {
        event.target.closest('tr').remove();
      }
    });
  })();
</script>
@endpush
