@extends('layouts.app')
@section('title','Edit Penerimaan')
@section('content')
  <div class="page-header"><div><h1 class="page-title">Edit Penerimaan</h1><p class="page-subtitle">{{ $receipt->receipt_number }}</p></div><a class="btn btn-outline btn-sm" href="{{ route('assets.receipts.show',$receipt) }}"><i class="bi bi-arrow-left"></i> Kembali</a></div>
  @include('hr.partials.alerts')
  @include('assets.receipts._form')
@endsection
