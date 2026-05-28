@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
  <div class="page-header">
    <div>
      <h1 class="page-title">Edit Supplier</h1>
      <p class="page-subtitle">{{ $supplier->name }}</p>
    </div>
    <a class="btn btn-outline btn-sm" href="{{ route('suppliers.index') }}"><i class="bi bi-arrow-left"></i> Kembali</a>
  </div>
  @include('hr.partials.alerts')
  @include('assets.suppliers._form')
@endsection
