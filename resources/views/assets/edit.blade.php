@extends('layouts.app')
@section('title','Edit Aset')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Edit Aset</h1><p class="page-subtitle">{{ $asset->asset_code }} - {{ $asset->name }}</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.show',$asset) }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets._form')
@endsection
