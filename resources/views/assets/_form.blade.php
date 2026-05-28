<div class="card">
  <form method="POST" action="{{ $asset->exists ? route('assets.update', $asset) : route('assets.store') }}" enctype="multipart/form-data">
    @csrf
    @if($asset->exists) @method('PUT') @endif
    <div class="modal-body">
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Kode Aset <span class="required">*</span></label><input class="form-control @error('asset_code') is-invalid @enderror" name="asset_code" value="{{ old('asset_code', $asset->asset_code) }}">@error('asset_code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label class="form-label">Nama Aset <span class="required">*</span></label><input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $asset->name) }}">@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><label class="form-label">Kategori</label><select class="form-select" name="asset_category_id"><option value="">Pilih kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('asset_category_id', $asset->asset_category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Lokasi</label><select class="form-select" name="asset_location_id"><option value="">Pilih lokasi</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('asset_location_id', $asset->asset_location_id)==$location->id)>{{ $location->name }}</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Merek</label><input class="form-control" name="brand" value="{{ old('brand', $asset->brand) }}"></div>
        <div class="form-group"><label class="form-label">Model</label><input class="form-control" name="model" value="{{ old('model', $asset->model) }}"></div>
        <div class="form-group"><label class="form-label">Serial Number</label><input class="form-control" name="serial_number" value="{{ old('serial_number', $asset->serial_number) }}"></div>
        <div class="form-group"><label class="form-label">Supplier</label><input class="form-control" name="supplier_name" value="{{ old('supplier_name', $asset->supplier_name) }}"></div>
        <div class="form-group"><label class="form-label">Tanggal Perolehan</label><input class="form-control" type="date" name="acquisition_date" value="{{ old('acquisition_date', $asset->acquisition_date?->format('Y-m-d')) }}"></div>
        <div class="form-group"><label class="form-label">Nilai Perolehan</label><input class="form-control js-money-input" type="text" inputmode="numeric" name="acquisition_value" value="{{ old('acquisition_value', $asset->acquisition_value) }}"></div>
        <div class="form-group"><label class="form-label">Sumber Aset <span class="required">*</span></label><select class="form-select" name="source_type">@foreach($sourceTypes as $source)<option value="{{ $source }}" @selected(old('source_type', $asset->source_type ?? 'existing_asset')===$source)>{{ str_replace('_',' ',ucfirst($source)) }}</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Kondisi <span class="required">*</span></label><select class="form-select" name="condition">@foreach($conditions as $condition)<option value="{{ $condition }}" @selected(old('condition', $asset->condition ?? 'good')===$condition)>{{ str_replace('_',' ',ucfirst($condition)) }}</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Status <span class="required">*</span></label><select class="form-select" name="status">@foreach($statuses as $status)<option value="{{ $status }}" @selected(old('status', $asset->status ?? 'active')===$status)>{{ str_replace('_',' ',ucfirst($status)) }}</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Penanggung Jawab</label><select class="form-select" name="responsible_user_id"><option value="">Pilih user</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('responsible_user_id', $asset->responsible_user_id)==$user->id)>{{ $user->name }}</option>@endforeach</select></div>
        <div class="form-group"><label class="form-label">Alat Ukur</label><label class="checkbox-label"><input type="checkbox" name="is_measuring_equipment" value="1" @checked(old('is_measuring_equipment', $asset->is_measuring_equipment))> Ya, alat ukur</label></div>
        <div class="form-group"><label class="form-label">Kalibrasi</label><label class="checkbox-label"><input type="checkbox" name="requires_calibration" value="1" @checked(old('requires_calibration', $asset->requires_calibration))> Perlu kalibrasi</label></div>
        <div class="form-group"><label class="form-label">Interval Kalibrasi (bulan)</label><input class="form-control" type="number" min="1" name="calibration_interval_months" value="{{ old('calibration_interval_months', $asset->calibration_interval_months) }}"></div>
        <div class="form-group"><label class="form-label">Kalibrasi Terakhir</label><input class="form-control" type="date" name="last_calibration_date" value="{{ old('last_calibration_date', $asset->last_calibration_date?->format('Y-m-d')) }}"></div>
        <div class="form-group"><label class="form-label">Kalibrasi Berikutnya</label><input class="form-control" type="date" name="next_calibration_date" value="{{ old('next_calibration_date', $asset->next_calibration_date?->format('Y-m-d')) }}"></div>
        <div class="form-group"><label class="form-label">Pemeriksaan Berkala</label><label class="checkbox-label"><input type="checkbox" name="requires_periodic_inspection" value="1" @checked(old('requires_periodic_inspection', $asset->requires_periodic_inspection))> Perlu pemeriksaan</label></div>
        <div class="form-group"><label class="form-label">Interval Pemeriksaan (bulan)</label><input class="form-control" type="number" min="1" name="inspection_interval_months" value="{{ old('inspection_interval_months', $asset->inspection_interval_months) }}"></div>
        <div class="form-group"><label class="form-label">Pemeriksaan Terakhir</label><input class="form-control" type="date" name="last_inspection_date" value="{{ old('last_inspection_date', $asset->last_inspection_date?->format('Y-m-d')) }}"></div>
        <div class="form-group"><label class="form-label">Pemeriksaan Berikutnya</label><input class="form-control" type="date" name="next_inspection_date" value="{{ old('next_inspection_date', $asset->next_inspection_date?->format('Y-m-d')) }}"></div>
        <div class="form-group form-group-full"><label class="form-label">Spesifikasi</label><textarea class="form-textarea" name="specification" rows="3">{{ old('specification', $asset->specification) }}</textarea></div>
        <div class="form-group form-group-full"><label class="form-label">Catatan</label><textarea class="form-textarea" name="notes" rows="3">{{ old('notes', $asset->notes) }}</textarea></div>
        <div class="form-group"><label class="form-label">Tipe Dokumen</label><select class="form-select" name="document_type"><option value="asset_photo">Foto aset</option><option value="manual_book">Manual book</option><option value="warranty">Warranty</option><option value="other">Lainnya</option></select></div>
        <div class="form-group">
          <label class="form-label">Upload Dokumen/Foto</label>
          <div class="file-upload-wrapper @error('document') has-error @enderror">
            <input type="file" id="asset-document-file" name="document" class="file-input @error('document') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" data-allowed-ext="pdf,jpg,jpeg,png,doc,docx" data-max-size="5">
            <label for="asset-document-file" class="file-label">
              <div class="file-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
              <div class="file-text">
                <span class="file-placeholder" data-default-placeholder="Klik untuk pilih file atau drag &amp; drop">Klik untuk pilih file atau drag &amp; drop</span>
                <span class="file-meta">PDF, gambar, Word - Maks. 5MB</span>
              </div>
            </label>
            <div class="file-preview"></div>
          </div>
          <div class="invalid-feedback file-error" @error('document') style="display: block;" @enderror>@error('document') {{ $message }} @enderror</div>
        </div>
        <div class="form-group form-group-full"><label class="form-label">Catatan Dokumen</label><textarea class="form-textarea" name="document_notes" rows="2">{{ old('document_notes') }}</textarea></div>
      </div>
    </div>
    <div class="modal-footer"><a class="btn btn-outline" href="{{ route('assets.index') }}">Batal</a><button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Simpan</button></div>
  </form>
</div>
