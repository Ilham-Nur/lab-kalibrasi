@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Data Karyawan</h1>
      <p class="page-subtitle">Kelola data utama, divisi, jabatan, dan status karyawan.</p>
    </div>
    <a class="btn btn-primary btn-sm" href="{{ route('hr.employees.create') }}"><i class="bi bi-plus-lg"></i> Tambah Data</a>
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="card-header">
      <form class="filter-row" method="GET">
        <input class="form-control" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIK, email, no HP">
        <select class="form-select" name="division_id">
          <option value="">Semua Divisi</option>
          @foreach ($divisions as $division)
            <option value="{{ $division->id }}" @selected(request('division_id') == $division->id)>{{ $division->name }}</option>
          @endforeach
        </select>
        <select class="form-select" name="position_id">
          <option value="">Semua Jabatan</option>
          @foreach ($positions as $position)
            <option value="{{ $position->id }}" @selected(request('position_id') == $position->id)>{{ $position->name }}</option>
          @endforeach
        </select>
        <select class="form-select" name="status_karyawan">
          <option value="">Semua Status</option>
          @foreach ($statuses as $status)
            <option value="{{ $status }}" @selected(request('status_karyawan') === $status)>{{ ucfirst($status) }}</option>
          @endforeach
        </select>
        <button class="btn btn-outline" type="submit"><i class="bi bi-search"></i> Filter</button>
      </form>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead>
          <tr>
            <th>Nama</th>
            <th>NIK</th>
            <th>Divisi</th>
            <th>Jabatan</th>
            <th>Status</th>
            <th>Kontak</th>
            <th class="th-aksi">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($employees as $employee)
            <tr>
              <td>
                <div class="td-name">{{ $employee->nama }}</div>
                <div class="td-email-sub">{{ $employee->tanggal_masuk?->format('d M Y') ?? 'Tanggal masuk kosong' }}</div>
              </td>
              <td>{{ $employee->nik_ktp }}</td>
              <td>{{ $employee->division?->name ?? '-' }}</td>
              <td>{{ $employee->position?->name ?? '-' }}</td>
              <td><span class="status-badge status-{{ $employee->status_karyawan }}">{{ ucfirst($employee->status_karyawan) }}</span></td>
              <td>
                <div>{{ $employee->no_hp ?: '-' }}</div>
                <div class="td-email-sub">{{ $employee->email ?: '-' }}</div>
              </td>
              <td>
                <div class="action-btns">
                  <a class="btn-action btn-view" href="{{ route('hr.employees.show', $employee) }}" title="Detail"><i class="bi bi-eye-fill"></i></a>
                  <a class="btn-action btn-edit" href="{{ route('hr.employees.edit', $employee) }}" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                  <form method="POST" action="{{ route('hr.employees.destroy', $employee) }}" data-confirm-delete="Hapus karyawan {{ $employee->nama }}?">
                    @csrf
                    @method('DELETE')
                    <button class="btn-action btn-delete" type="submit" title="Hapus"><i class="bi bi-trash3-fill"></i></button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7">
                <div class="empty-state">
                  <div class="empty-icon"><i class="bi bi-people"></i></div>
                  <div class="empty-title">Belum ada data karyawan</div>
                  <div class="empty-desc">Klik Tambah Data untuk membuat karyawan pertama.</div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="table-footer">{{ $employees->links() }}</div>
  </div>
@endsection
