@extends('layouts.app')
@section('title','Buat Penerimaan')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Buat Penerimaan</h1><p class="page-subtitle">Catat barang yang diterima dari proses pengadaan.</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.receipts.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.receipts._form')
@endsection
