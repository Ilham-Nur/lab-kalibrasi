@extends('layouts.app')
@section('title','Buat Pemeriksaan')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Buat Pemeriksaan</h1><p class="page-subtitle">Isi checklist dan kesimpulan pemeriksaan berkala.</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.inspections.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.inspections._form')
@endsection
