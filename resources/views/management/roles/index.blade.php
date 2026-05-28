@extends('layouts.app')

@section('title', 'Manajemen Role')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Manajemen Role</h1>
      <p class="page-subtitle">Kelola role dan permission yang mengatur akses menu serta approval.</p>
    </div>
    @can('roles.create')
      <a class="btn btn-primary btn-sm" href="{{ route('management.roles.create') }}"><i class="bi bi-plus-lg"></i> Tambah Role</a>
    @endcan
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Role</th><th>Jumlah User</th><th>Permission</th><th class="th-aksi">Aksi</th></tr></thead>
        <tbody>
          @forelse ($roles as $role)
            <tr>
              <td><strong>{{ $role->name }}</strong></td>
              <td>{{ $role->users_count }}</td>
              <td>{{ $role->permissions_count }}</td>
              <td>
                <div class="action-btns">
                  @can('roles.edit')
                    <a class="btn-action btn-edit" href="{{ route('management.roles.edit', $role) }}"><i class="bi bi-pencil-fill"></i></a>
                  @endcan
                  @can('roles.delete')
                    <form method="POST" action="{{ route('management.roles.destroy', $role) }}" data-confirm-delete="Hapus role {{ $role->name }}?">
                      @csrf @method('DELETE')
                      <button class="btn-action btn-delete"><i class="bi bi-trash3-fill"></i></button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="4"><div class="empty-state"><div class="empty-title">Belum ada role</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('hr.partials.pagination', ['paginator' => $roles])
  </div>
@endsection
