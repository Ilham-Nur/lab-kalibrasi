@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Tambah Supplier</h1>
      <p class="page-subtitle">Data awal vendor untuk pengadaan dan penerimaan barang.</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('suppliers.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>
  @include('hr.partials.alerts')
  @include('assets.suppliers._form')
@endsection
