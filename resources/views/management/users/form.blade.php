@extends('layouts.app')

@section('title', $managedUser->exists ? 'Edit User' : 'Tambah User')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">{{ $managedUser->exists ? 'Edit User' : 'Tambah User' }}</h1>
      <p class="page-subtitle">Atur identitas akun dan role yang dimiliki user.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('management.users.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <form method="POST" action="{{ $managedUser->exists ? route('management.users.update', $managedUser) : route('management.users.store') }}">
      @csrf
      @if ($managedUser->exists) @method('PUT') @endif
      <div class="modal-body">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nama <span class="required">*</span></label>
            <input class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $managedUser->name) }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Username <span class="required">*</span></label>
            <input class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username', $managedUser->username) }}">
            @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Email</label>
            <input class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email', $managedUser->email) }}">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Password {{ $managedUser->exists ? '' : '*' }}</label>
            <input class="form-control @error('password') is-invalid @enderror" type="password" name="password">
            <div class="form-help">{{ $managedUser->exists ? 'Kosongkan jika tidak ingin mengganti password.' : 'Wajib diisi untuk user baru.' }}</div>
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="form-group form-group-full">
            <label class="form-label">Role</label>
            <div class="required-doc-grid">
              @foreach ($roles as $role)
                <label class="required-doc-item {{ in_array($role->name, old('roles', $selectedRoles), true) ? 'is-complete' : '' }}">
                  <input type="checkbox" name="roles[]" value="{{ $role->name }}" @checked(in_array($role->name, old('roles', $selectedRoles), true))>
                  <span>{{ $role->name }}</span>
                  <strong>{{ $role->permissions_count ?? '' }}</strong>
                </label>
              @endforeach
            </div>
            @error('roles') <div class="invalid-feedback" style="display:block">{{ $message }}</div> @enderror
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a class="btn btn-outline" href="{{ route('management.users.index') }}">Batal</a>
        <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Simpan</button>
      </div>
    </form>
  </div>
@endsection
