@extends('layouts.app')

@section('title', $position->exists ? 'Edit Jabatan' : 'Tambah Jabatan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $position->exists ? 'Edit Jabatan' : 'Tambah Jabatan' }}</h1>
      <p class="page-subtitle">Lengkapi relasi divisi, nama jabatan, status, dan deskripsi.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('hr.positions.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <form method="POST" action="{{ $position->exists ? route('hr.positions.update', $position) : route('hr.positions.store') }}">
      @csrf
      @if ($position->exists)
        @method('PUT')
      @endif

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Divisi <span class="required">*</span></label>
            <select class="form-select @error('division_id') is-invalid @enderror" name="division_id">
              <option value="">Pilih Divisi</option>
              @foreach ($divisions as $division)
                <option value="{{ $division->id }}" @selected(old('division_id', $position->division_id) == $division->id)>{{ $division->name }}</option>
              @endforeach
            </select>
            @error('division_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Nama Jabatan <span class="required">*</span></label>
            <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $position->name) }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Status <span class="required">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" name="status">
              @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $position->status ?? 'aktif') === $status)>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group form-group-full">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-textarea @error('description') is-invalid @enderror" name="description" rows="4">{{ old('description', $position->description) }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <a class="btn btn-outline" href="{{ route('hr.positions.index') }}">Batal</a>
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Simpan</button>
      </div>
    </form>
  </div>
@endsection
