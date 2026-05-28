@extends('layouts.app')
@section('title','Edit Pengadaan')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Edit Pengadaan</h1><p class="page-subtitle">{{ $procurement->procurement_number }}</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.procurements.show',$procurement) }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.procurements._form')
@endsection
