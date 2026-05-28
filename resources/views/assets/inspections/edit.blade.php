@extends('layouts.app')
@section('title','Edit Pemeriksaan')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Edit Pemeriksaan</h1><p class="page-subtitle">{{ $inspection->inspection_number }}</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.inspections.show',$inspection) }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.inspections._form')
@endsection
