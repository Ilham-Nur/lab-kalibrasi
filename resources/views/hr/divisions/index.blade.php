@extends('layouts.app')

@section('title', 'Divisi')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Divisi</h1>
      <p class="page-subtitle">Kelola master divisi untuk karyawan, jabatan, recruitment, dan jobdesk.</p>
    </div>
    <div class="action-btns">
      <a class="btn btn-outline btn-sm" href="{{ route('hr.job-descriptions.index') }}"><i class="bi bi-arrow-left"></i> Jobdesk</a>
      <a class="btn btn-primary btn-sm" href="{{ route('hr.divisions.create') }}"><i class="bi bi-plus-lg"></i> Tambah Divisi</a>
    </div>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="card-header">
      <form class="filter-row">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama divisi">
        <select class="form-select" name="status">
          <option value="">Semua Status</option>
          @foreach ($statuses as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
          @endforeach
        </select>
        <button class="btn btn-outline"><i class="bi bi-search"></i> Filter</button>
      </form>
    </div>

    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama Divisi</th>
            <th>Deskripsi</th>
            <th>Jabatan</th>
            <th>Karyawan</th>
            <th>Status</th>
            <th class="th-aksi">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($divisions as $division)
            <tr>
              <td><strong>{{ $division->name }}</strong></td>
              <td>{{ $division->description ?: '-' }}</td>
              <td>{{ $division->positions_count }}</td>
              <td>{{ $division->employees_count }}</td>
              <td><span class="status-badge status-{{ $division->status }}">{{ ucfirst($division->status) }}</span></td>
              <td>
                <div class="action-btns">
                  <a class="btn-action btn-edit" href="{{ route('hr.divisions.edit', $division) }}"><i class="bi bi-pencil-fill"></i></a>
                  <form method="POST" action="{{ route('hr.divisions.destroy', $division) }}" data-confirm-delete="Hapus divisi {{ $division->name }}?">
                    @csrf
                    @method('DELETE')
                    <button class="btn-action btn-delete" type="submit"><i class="bi bi-trash3-fill"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6"><div class="empty-state"><div class="empty-title">Belum ada divisi</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('hr.partials.pagination', ['paginator' => $divisions])
  </div>
@endsection
