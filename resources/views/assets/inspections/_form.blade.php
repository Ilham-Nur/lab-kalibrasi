<div class="card">
  <form method="POST" action="{{ $inspection->exists ? route('assets.inspections.update',$inspection) : route('assets.inspections.store') }}">
    @csrf @if($inspection->exists) @method('PUT') @endif
    <div class="modal-body"><div class="form-grid">
      <div class="form-group"><label class="form-label">Aset</label><select class="form-select" name="asset_id" required><option value="">Pilih aset</option>@foreach($assets as $asset)<option value="{{ $asset->id }}" @selected(old('asset_id',$inspection->asset_id)==$asset->id)>{{ $asset->asset_code }} - {{ $asset->name }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Tanggal Pemeriksaan</label><input class="form-control" type="date" name="inspection_date" value="{{ old('inspection_date',$inspection->inspection_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
      <div class="form-group"><label class="form-label">Next Inspection</label><input class="form-control" type="date" name="next_inspection_date" value="{{ old('next_inspection_date',$inspection->next_inspection_date?->format('Y-m-d')) }}"></div>
      <div class="form-group"><label class="form-label">Petugas</label><select class="form-select" name="inspected_by"><option value="">User login</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('inspected_by',$inspection->inspected_by)==$user->id)>{{ $user->name }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Result</label><select class="form-select" name="result">@foreach($results as $result)<option value="{{ $result }}" @selected(old('result',$inspection->result ?? 'pass')===$result)>{{ str_replace('_',' ',ucfirst($result)) }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Status</label><select class="form-select" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status',$inspection->status ?? 'completed')===$status)>{{ str_replace('_',' ',ucfirst($status)) }}</option>@endforeach</select></div>
      <div class="form-group form-group-full"><label class="form-label">Catatan</label><textarea class="form-textarea" name="notes">{{ old('notes',$inspection->notes) }}</textarea></div>
    </div></div>
    <div class="card-header"><h2 class="card-title">Checklist</h2><button class="btn btn-outline btn-sm" type="button" id="add-checklist-row"><i class="bi bi-plus-lg"></i> Tambah Row</button></div>
    <div class="table-responsive"><table class="data-table" id="checklist-table"><thead><tr><th>Checklist</th><th>Result</th><th>Notes</th><th></th></tr></thead><tbody>
      @php($rows = old('items', $inspection->items?->count() ? $inspection->items->toArray() : collect($defaultChecklist)->map(fn($name)=>['checklist_name'=>$name,'result'=>'ok','notes'=>''])->toArray()))
      @foreach($rows as $i => $item)
        <tr><td><input class="form-control" name="items[{{ $i }}][checklist_name]" value="{{ $item['checklist_name'] ?? '' }}"></td><td><select class="form-select" name="items[{{ $i }}][result]">@foreach($itemResults as $result)<option value="{{ $result }}" @selected(($item['result'] ?? 'ok')===$result)>{{ str_replace('_',' ',ucfirst($result)) }}</option>@endforeach</select></td><td><input class="form-control" name="items[{{ $i }}][notes]" value="{{ $item['notes'] ?? '' }}"></td><td><button class="btn-action btn-delete remove-row" type="button"><i class="bi bi-trash3-fill"></i></button></td></tr>
      @endforeach
    </tbody></table></div>
    <div class="modal-footer"><a class="btn btn-outline" href="{{ route('assets.inspections.index') }}">Batal</a><button class="btn btn-primary">Simpan</button></div>
  </form>
</div>
@push('scripts')
<script>
  (function(){var index=document.querySelectorAll('#checklist-table tbody tr').length;document.getElementById('add-checklist-row')?.addEventListener('click',function(){var row=document.createElement('tr');row.innerHTML='<td><input class="form-control" name="items['+index+'][checklist_name]"></td><td><select class="form-select" name="items['+index+'][result]"><option value="ok">Ok</option><option value="not_ok">Not ok</option><option value="not_applicable">Not applicable</option></select></td><td><input class="form-control" name="items['+index+'][notes]"></td><td><button class="btn-action btn-delete remove-row" type="button"><i class="bi bi-trash3-fill"></i></button></td>';document.querySelector('#checklist-table tbody').appendChild(row);index++;});document.addEventListener('click',function(e){if(e.target.closest('.remove-row')&&document.querySelectorAll('#checklist-table tbody tr').length>1){e.target.closest('tr').remove();}});})();
</script>
@endpush
