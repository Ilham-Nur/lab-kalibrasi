@extends('layouts.app')
@section('title','Buat Kalibrasi')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Buat Kalibrasi</h1><p class="page-subtitle">Pilih aset yang requires calibration dan isi hasil evaluasi.</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.calibrations.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.calibrations._form')
@endsection
