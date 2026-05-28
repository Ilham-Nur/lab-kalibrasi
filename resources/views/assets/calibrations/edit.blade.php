@extends('layouts.app')
@section('title','Edit Kalibrasi')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Edit Kalibrasi</h1><p class="page-subtitle">{{ $calibration->calibration_number }}</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.calibrations.show',$calibration) }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.calibrations._form')
@endsection
