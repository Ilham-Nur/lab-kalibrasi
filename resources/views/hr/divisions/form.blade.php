@extends('layouts.app')

@section('title', $division->exists ? 'Edit Divisi' : 'Tambah Divisi')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $division->exists ? 'Edit Divisi' : 'Tambah Divisi' }}</h1>
      <p class="page-subtitle">Lengkapi nama, status, dan deskripsi divisi.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('hr.divisions.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <form method="POST" action="{{ $division->exists ? route('hr.divisions.update', $division) : route('hr.divisions.store') }}">
      @csrf
      @if ($division->exists)
        @method('PUT')
      @endif

      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nama Divisi <span class="required">*</span></label>
            <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $division->name) }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Status <span class="required">*</span></label>
            <select class="form-select @error('status') is-invalid @enderror" name="status">
              @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected(old('status', $division->status ?? 'aktif') === $status)>{{ ucfirst($status) }}</option>
              @endforeach
            </select>
            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group form-group-full">
            <label class="form-label">Deskripsi</label>
            <textarea class="form-textarea @error('description') is-invalid @enderror" name="description" rows="4">{{ old('description', $division->description) }}</textarea>
            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <a class="btn btn-outline" href="{{ route('hr.divisions.index') }}">Batal</a>
        <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle"></i> Simpan</button>
      </div>
    </form>
  </div>
@endsection
