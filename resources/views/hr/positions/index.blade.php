@extends('layouts.app')

@section('title', 'Jabatan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Jabatan</h1>
      <p class="page-subtitle">Kelola master jabatan yang terhubung ke divisi dan jobdesk.</p>
    </div>
    <div class="action-btns">
      <a class="btn btn-outline btn-sm" href="{{ route('hr.job-descriptions.index') }}"><i class="bi bi-arrow-left"></i> Jobdesk</a>
      <a class="btn btn-primary btn-sm" href="{{ route('hr.positions.create') }}"><i class="bi bi-plus-lg"></i> Tambah Jabatan</a>
    </div>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="card-header">
      <form class="filter-row">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama jabatan">
        <select class="form-select" name="division_id">
          <option value="">Semua Divisi</option>
          @foreach ($divisions as $division)
            <option value="{{ $division->id }}" @selected(request('division_id') == $division->id)>{{ $division->name }}</option>
          @endforeach
        </select>
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
            <th>Nama Jabatan</th>
            <th>Divisi</th>
            <th>Deskripsi</th>
            <th>Karyawan</th>
            <th>Status</th>
            <th class="th-aksi">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($positions as $position)
            <tr>
              <td><strong>{{ $position->name }}</strong></td>
              <td>{{ $position->division?->name ?? '-' }}</td>
              <td>{{ $position->description ?: '-' }}</td>
              <td>{{ $position->employees_count }}</td>
              <td><span class="status-badge status-{{ $position->status }}">{{ ucfirst($position->status) }}</span></td>
              <td>
                <div class="action-btns">
                  <a class="btn-action btn-edit" href="{{ route('hr.positions.edit', $position) }}"><i class="bi bi-pencil-fill"></i></a>
                  <form method="POST" action="{{ route('hr.positions.destroy', $position) }}" data-confirm-delete="Hapus jabatan {{ $position->name }}?">
                    @csrf
                    @method('DELETE')
                    <button class="btn-action btn-delete" type="submit"><i class="bi bi-trash3-fill"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6"><div class="empty-state"><div class="empty-title">Belum ada jabatan</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('hr.partials.pagination', ['paginator' => $positions])
  </div>
@endsection
