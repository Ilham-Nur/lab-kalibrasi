@extends('layouts.app')

@section('title', 'Supplier / Vendor')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Supplier / Vendor</h1>
      <p class="page-subtitle">Master supplier untuk pengadaan dan penerimaan barang aset.</p>
    </div>
    @can('asset-suppliers.create')
      <a class="btn btn-primary btn-sm" href="{{ route('suppliers.create') }}"><i class="bi bi-plus-lg"></i> Tambah Supplier</a>
    @endcan
  </div>

  @include('hr.partials.alerts')

  <div class="card">
    <div class="card-header">
      <form class="filter-row">
        <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari supplier, kontak, email">
        <select class="form-select" name="status">
          <option value="">Semua Status</option>
          @foreach($statuses as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
          @endforeach
        </select>
        <button class="btn btn-outline"><i class="bi bi-search"></i> Filter</button>
      </form>
    </div>
    <div class="table-responsive">
      <table class="data-table">
        <thead><tr><th>Supplier</th><th>Kontak</th><th>Email</th><th>Status</th><th>Catatan</th><th class="th-aksi">Aksi</th></tr></thead>
        <tbody>
          @forelse($suppliers as $supplier)
            <tr>
              <td><strong>{{ $supplier->name }}</strong><div class="td-email-sub">{{ $supplier->address ?: '-' }}</div></td>
              <td>{{ $supplier->contact_person ?: '-' }}<div class="td-email-sub">{{ $supplier->phone ?: '-' }}</div></td>
              <td>{{ $supplier->email ?: '-' }}</td>
              <td><span class="status-badge status-{{ $supplier->status }}">{{ ucfirst($supplier->status) }}</span></td>
              <td>{{ $supplier->notes ?: '-' }}</td>
              <td>
                <div class="action-btns">
                  @can('asset-suppliers.edit')
                    <a class="btn-action btn-edit" href="{{ route('suppliers.edit', $supplier) }}"><i class="bi bi-pencil-fill"></i></a>
                  @endcan
                  @can('asset-suppliers.delete')
                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" data-confirm-delete="Hapus supplier {{ $supplier->name }}?">
                      @csrf
                      @method('DELETE')
                      <button class="btn-action btn-delete"><i class="bi bi-trash3-fill"></i></button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="6"><div class="empty-state"><div class="empty-title">Belum ada supplier</div></div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @include('hr.partials.pagination', ['paginator' => $suppliers])
  </div>
@endsection
