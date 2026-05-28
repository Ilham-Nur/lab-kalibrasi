@extends('layouts.app')

@section('title', $role->exists ? 'Edit Role' : 'Tambah Role')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $role->exists ? 'Edit Role' : 'Tambah Role' }}</h1>
      <p class="page-subtitle">Pilih permission yang boleh dilakukan oleh role ini.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('management.roles.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <form method="POST" action="{{ $role->exists ? route('management.roles.update', $role) : route('management.roles.store') }}">
      @csrf
      @if ($role->exists) @method('PUT') @endif
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nama Role <span class="required">*</span></label>
            <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $role->name) }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>

      @foreach ($permissionGroups as $group => $permissions)
        <div class="card-header"><h2 class="card-title">{{ str_replace('-', ' ', ucfirst($group)) }}</h2></div>
        <div class="modal-body">
          <div class="required-doc-grid">
            @foreach ($permissions as $permission)
              <label class="required-doc-item {{ in_array($permission->name, old('permissions', $selectedPermissions), true) ? 'is-complete' : '' }}">
                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, old('permissions', $selectedPermissions), true))>
                <span>{{ $permission->name }}</span>
                <strong>{{ str($permission->name)->after('.') }}</strong>
              </label>
            @endforeach
          </div>
        </div>
      @endforeach

      <div class="modal-footer">
        <a class="btn btn-outline" href="{{ route('management.roles.index') }}">Batal</a>
        <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Simpan</button>
      </div>
    </form>
  </div>
@endsection
