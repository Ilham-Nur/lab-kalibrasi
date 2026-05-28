@extends('layouts.app')
@section('title','Buat Pengadaan')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Buat Pengadaan</h1><p class="page-subtitle">Header pengadaan dan detail item estimasi.</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.procurements.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.procurements._form')
@endsection
