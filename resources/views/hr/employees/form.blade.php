@extends('layouts.app')

@section('title', $employee->exists ? 'Edit Karyawan' : 'Tambah Karyawan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $employee->exists ? 'Edit Karyawan' : 'Tambah Karyawan' }}</h1>
      <p class="page-subtitle">Lengkapi profil, identitas, jabatan, dan nomor administrasi karyawan.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('hr.employees.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <form method="POST" action="{{ $employee->exists ? route('hr.employees.update', $employee) : route('hr.employees.store') }}" enctype="multipart/form-data">
      @csrf
      @if ($employee->exists)
        @method('PUT')
      @endif

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nama <span class="required">*</span></label>
            <input class="form-control @error('nama') is-invalid @enderror" name="nama" value="{{ old('nama', $employee->nama) }}">
            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">NIK KTP <span class="required">*</span></label>
            <input class="form-control @error('nik_ktp') is-invalid @enderror" name="nik_ktp" value="{{ old('nik_ktp', $employee->nik_ktp) }}">
            @error('nik_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Tempat Lahir</label>
            <input class="form-control" name="tempat_lahir" value="{{ old('tempat_lahir', $employee->tempat_lahir) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal Lahir</label>
            <input class="form-control" type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $employee->tanggal_lahir?->format('Y-m-d')) }}">
          </div>
          <div class="form-group">
            <label class="form-label">No HP</label>
            <input class="form-control" name="no_hp" value="{{ old('no_hp', $employee->no_hp) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $employee->email) }}">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Jenis Kelamin</label>
            <select class="form-select" name="jenis_kelamin">
              <option value="">Pilih</option>
              @foreach ($genders as $gender)
                <option value="{{ $gender }}" @selected(old('jenis_kelamin', $employee->jenis_kelamin) === $gender)>{{ ucfirst($gender) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status Pernikahan</label>
            <select class="form-select" name="status_pernikahan" id="status_pernikahan">
              <option value="">Pilih</option>
              @foreach ($maritalStatuses as $status)
                <option value="{{ $status }}" @selected(old('status_pernikahan', $employee->status_pernikahan) === $status)>{{ str_replace('_', ' ', ucfirst($status)) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group" id="spouse_nik_group">
            <label class="form-label">NIK KTP Istri/Suami</label>
            <input class="form-control @error('spouse_nik_ktp') is-invalid @enderror" name="spouse_nik_ktp" value="{{ old('spouse_nik_ktp', $employee->spouse_nik_ktp) }}">
            @error('spouse_nik_ktp') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Jumlah Anak</label>
            <input class="form-control" type="number" min="0" name="jumlah_anak" id="jumlah_anak" value="{{ old('jumlah_anak', $employee->jumlah_anak ?? 0) }}">
          </div>
          <div class="form-group form-group-full" id="children_nik_group">
            <label class="form-label">NIK Anak</label>
            <textarea class="form-textarea @error('children_nik_text') is-invalid @enderror" name="children_nik_text" rows="3" placeholder="Isi satu NIK anak per baris">{{ $childrenNikText }}</textarea>
            <div class="form-help">Jika jumlah anak lebih dari 0, isi minimal sesuai jumlah anak.</div>
            @error('children_nik_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Tanggal Masuk</label>
            <input class="form-control" type="date" name="tanggal_masuk" value="{{ old('tanggal_masuk', $employee->tanggal_masuk?->format('Y-m-d')) }}">
          </div>
          <div class="form-group">
            <label class="form-label">Divisi</label>
            <select class="form-select" name="division_id">
              <option value="">Pilih Divisi</option>
              @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected(old('division_id', $employee->division_id) == $division->id)>{{ $division->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Jabatan</label>
            <select class="form-select" name="position_id">
              <option value="">Pilih Jabatan</option>
              @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected(old('position_id', $employee->position_id) == $position->id)>{{ $position->name }} @if($position->division) - {{ $position->division->name }} @endif</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Status Karyawan</label>
            <select class="form-select" name="status_karyawan">
              @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status_karyawan', $employee->status_karyawan ?? 'aktif') === $status)>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Foto</label>
            <input class="form-control @error('foto') is-invalid @enderror" type="file" name="foto" accept=".jpg,.jpeg,.png">
            @error('foto') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">No NPWP</label>
            <input class="form-control" name="no_npwp" value="{{ old('no_npwp', $employee->no_npwp) }}">
          </div>
          <div class="form-group">
            <label class="form-label">No BPJS Kesehatan</label>
            <input class="form-control" name="no_bpjs_kesehatan" value="{{ old('no_bpjs_kesehatan', $employee->no_bpjs_kesehatan) }}">
          </div>
          <div class="form-group">
            <label class="form-label">No BPJS Ketenagakerjaan</label>
            <input class="form-control" name="no_bpjs_ketenagakerjaan" value="{{ old('no_bpjs_ketenagakerjaan', $employee->no_bpjs_ketenagakerjaan) }}">
          </div>
          <div class="form-group form-group-full">
            <label class="form-label">Alamat</label>
            <textarea class="form-textarea" name="alamat" rows="3">{{ old('alamat', $employee->alamat) }}</textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-outline" href="{{ route('hr.employees.index') }}">Batal</a>
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Simpan</button>
      </div>
    </form>
  </div>
@endsection

@push('scripts')
  <script>
    (function () {
      function syncFamilyFields() {
        var marital = document.getElementById('status_pernikahan')?.value;
        var childCount = parseInt(document.getElementById('jumlah_anak')?.value || '0', 10);
        var spouseGroup = document.getElementById('spouse_nik_group');
        var childrenGroup = document.getElementById('children_nik_group');

        if (spouseGroup) spouseGroup.style.display = marital === 'menikah' ? '' : 'none';
        if (childrenGroup) childrenGroup.style.display = childCount > 0 ? '' : 'none';
      }

      document.getElementById('status_pernikahan')?.addEventListener('change', syncFamilyFields);
      document.getElementById('jumlah_anak')?.addEventListener('input', syncFamilyFields);
      syncFamilyFields();
    })();
  </script>
@endpush
