@extends('layouts.app')
@section('title','Tambah Aset Manual')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Tambah Aset Manual</h1><p class="page-subtitle">Input aset existing atau aset baru yang tidak berasal dari pengadaan.</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets._form')
@endsection
