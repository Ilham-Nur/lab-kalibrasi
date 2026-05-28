<div class="card">
  <form method="POST" action="{{ $calibration->exists ? route('assets.calibrations.update',$calibration) : route('assets.calibrations.store') }}" enctype="multipart/form-data">
    @csrf @if($calibration->exists) @method('PUT') @endif
    <div class="modal-body"><div class="form-grid">
      <div class="form-group"><label class="form-label">Aset</label><select class="form-select" name="asset_id" required><option value="">Pilih aset kalibrasi</option>@foreach($assets as $asset)<option value="{{ $asset->id }}" @selected(old('asset_id',$calibration->asset_id)==$asset->id)>{{ $asset->asset_code }} - {{ $asset->name }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Tanggal Kalibrasi</label><input class="form-control" type="date" name="calibration_date" value="{{ old('calibration_date',$calibration->calibration_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"></div>
      <div class="form-group"><label class="form-label">Next Kalibrasi</label><input class="form-control" type="date" name="next_calibration_date" value="{{ old('next_calibration_date',$calibration->next_calibration_date?->format('Y-m-d')) }}"></div>
      <div class="form-group"><label class="form-label">Jenis</label><select class="form-select" name="calibration_type">@foreach($types as $type)<option value="{{ $type }}" @selected(old('calibration_type',$calibration->calibration_type ?? 'internal')===$type)>{{ ucfirst($type) }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Provider</label><input class="form-control" name="calibration_provider" value="{{ old('calibration_provider',$calibration->calibration_provider) }}"></div>
      <div class="form-group"><label class="form-label">Nomor Sertifikat</label><input class="form-control" name="certificate_number" value="{{ old('certificate_number',$calibration->certificate_number) }}"></div>
      <div class="form-group"><label class="form-label">Result</label><select class="form-select" name="result">@foreach($results as $result)<option value="{{ $result }}" @selected(old('result',$calibration->result ?? 'pass')===$result)>{{ str_replace('_',' ',ucfirst($result)) }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Status</label><select class="form-select" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status',$calibration->status ?? 'completed')===$status)>{{ str_replace('_',' ',ucfirst($status)) }}</option>@endforeach</select></div>
      <div class="form-group"><label class="form-label">Evaluated By</label><select class="form-select" name="evaluated_by"><option value="">User login</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('evaluated_by',$calibration->evaluated_by)==$user->id)>{{ $user->name }}</option>@endforeach</select></div>
      <div class="form-group">
        <label class="form-label">Upload Sertifikat</label>
        <div class="file-upload-wrapper @error('file_certificate') has-error @enderror">
          <input type="file" id="asset-calibration-certificate-file" name="file_certificate" class="file-input @error('file_certificate') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png" data-allowed-ext="pdf,jpg,jpeg,png" data-max-size="5">
          <label for="asset-calibration-certificate-file" class="file-label">
            <div class="file-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
            <div class="file-text">
              <span class="file-placeholder" data-default-placeholder="Klik untuk pilih sertifikat atau drag &amp; drop">Klik untuk pilih sertifikat atau drag &amp; drop</span>
              <span class="file-meta">PDF atau gambar - Maks. 5MB</span>
            </div>
          </label>
          <div class="file-preview"></div>
        </div>
        <div class="invalid-feedback file-error" @error('file_certificate') style="display: block;" @enderror>@error('file_certificate') {{ $message }} @enderror</div>
        @if($calibration->certificate_url)<div class="form-help"><a href="{{ $calibration->certificate_url }}" target="_blank">Lihat sertifikat saat ini</a></div>@endif
      </div>
      <div class="form-group form-group-full"><label class="form-label">Evaluasi</label><textarea class="form-textarea" name="evaluation_notes">{{ old('evaluation_notes',$calibration->evaluation_notes) }}</textarea></div>
    </div></div>
    <div class="card-header"><h2 class="card-title">Detail Hasil Kalibrasi</h2><button class="btn btn-outline btn-sm" type="button" id="add-result-row"><i class="bi bi-plus-lg"></i> Tambah Row</button></div>
    <div class="table-responsive"><table class="data-table" id="calibration-results-table"><thead><tr><th>Parameter</th><th>Nominal</th><th>Measured</th><th>Correction</th><th>Uncertainty</th><th>Tolerance</th><th>Result</th><th>Notes</th><th></th></tr></thead><tbody>
      @php($rows = old('results', $calibration->results?->count() ? $calibration->results->toArray() : [['parameter'=>'','nominal_value'=>'','measured_value'=>'','correction'=>'','uncertainty'=>'','tolerance'=>'','result'=>'','notes'=>'']]))
      @foreach($rows as $i => $item)
        <tr><td><input class="form-control" name="results[{{ $i }}][parameter]" value="{{ $item['parameter'] ?? '' }}"></td><td><input class="form-control" name="results[{{ $i }}][nominal_value]" value="{{ $item['nominal_value'] ?? '' }}"></td><td><input class="form-control" name="results[{{ $i }}][measured_value]" value="{{ $item['measured_value'] ?? '' }}"></td><td><input class="form-control" name="results[{{ $i }}][correction]" value="{{ $item['correction'] ?? '' }}"></td><td><input class="form-control" name="results[{{ $i }}][uncertainty]" value="{{ $item['uncertainty'] ?? '' }}"></td><td><input class="form-control" name="results[{{ $i }}][tolerance]" value="{{ $item['tolerance'] ?? '' }}"></td><td><input class="form-control" name="results[{{ $i }}][result]" value="{{ $item['result'] ?? '' }}"></td><td><input class="form-control" name="results[{{ $i }}][notes]" value="{{ $item['notes'] ?? '' }}"></td><td><button class="btn-action btn-delete remove-row" type="button"><i class="bi bi-trash3-fill"></i></button></td></tr>
      @endforeach
    </tbody></table></div>
    <div class="modal-footer"><a class="btn btn-outline" href="{{ route('assets.calibrations.index') }}">Batal</a><button class="btn btn-primary">Simpan</button></div>
  </form>
</div>
@push('scripts')
<script>
  (function(){var index=document.querySelectorAll('#calibration-results-table tbody tr').length;document.getElementById('add-result-row')?.addEventListener('click',function(){var row=document.createElement('tr');row.innerHTML='<td><input class="form-control" name="results['+index+'][parameter]"></td><td><input class="form-control" name="results['+index+'][nominal_value]"></td><td><input class="form-control" name="results['+index+'][measured_value]"></td><td><input class="form-control" name="results['+index+'][correction]"></td><td><input class="form-control" name="results['+index+'][uncertainty]"></td><td><input class="form-control" name="results['+index+'][tolerance]"></td><td><input class="form-control" name="results['+index+'][result]"></td><td><input class="form-control" name="results['+index+'][notes]"></td><td><button class="btn-action btn-delete remove-row" type="button"><i class="bi bi-trash3-fill"></i></button></td>';document.querySelector('#calibration-results-table tbody').appendChild(row);index++;});document.addEventListener('click',function(e){if(e.target.closest('.remove-row')&&document.querySelectorAll('#calibration-results-table tbody tr').length>1){e.target.closest('tr').remove();}});})();
</script>
@endpush
