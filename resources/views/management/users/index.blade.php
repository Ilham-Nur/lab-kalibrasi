@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Manajemen User</h1>
      <p class="page-subtitle">Kelola akun login dan role akses aplikasi.</p>
    </div>
    @can('users.create')
      <a class="btn btn-primary btn-sm" href="{{ route('management.users.create') }}"><i class="bi bi-plus-lg"></i> Tambah User</a>
    @endcan
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="card-header">
      <form class="filter-row">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, email">
        <select class="form-select" name="role">
          <option value="">Semua Role</option>
          @foreach ($roles as $role)
            <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
          @endforeach
        </select>
        <button class="btn btn-outline"><i class="bi bi-search"></i> Filter</button>
      </form>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th class="th-aksi">Aksi</th></tr></thead>
        <tbody>
          @forelse ($users as $user)
            <tr>
              <td><strong>{{ $user->name }}</strong></td>
              <td>{{ $user->username }}</td>
              <td>{{ $user->email ?: '-' }}</td>
              <td>
                @forelse ($user->roles as $role)
                  <span class="badge badge-info">{{ $role->name }}</span>
                @empty
                  <span class="badge badge-secondary">Belum ada role</span>
                @endforelse
              </td>
              <td>
                <div class="action-btns">
                  @can('users.edit')
                    <a class="btn-action btn-edit" href="{{ route('management.users.edit', $user) }}"><i class="bi bi-pencil-fill"></i></a>
                  @endcan
                  @can('users.delete')
                    <form method="POST" action="{{ route('management.users.destroy', $user) }}" data-confirm-delete="Hapus user {{ $user->name }}?">
                      @csrf @method('DELETE')
                      <button class="btn-action btn-delete"><i class="bi bi-trash3-fill"></i></button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="5"><div class="empty-state"><div class="empty-title">Belum ada user</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('hr.partials.pagination', ['paginator' => $users])
  </div>
@endsection
